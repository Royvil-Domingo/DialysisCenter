<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Charge;
use App\Models\Patient;
use App\Models\TreatmentRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function actingAsClinicAdmin(): User
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin);

        return $admin;
    }

    protected function makePatient(array $overrides = []): Patient
    {
        return Patient::create(array_merge([
            'first_name' => 'Test',
            'last_name' => 'Patient',
            'birthdate' => '2000-01-01',
            'sex' => 'female',
            'contact_number' => '09170000000',
            'status' => 'active',
        ], $overrides));
    }

    public function test_patient_reference_codes_have_high_entropy_and_are_unique(): void
    {
        $codes = [];

        for ($index = 0; $index < 20; $index++) {
            $codes[] = $this->makePatient(['first_name' => 'Patient'.$index])->patient_code;
        }

        foreach ($codes as $code) {
            $this->assertMatchesRegularExpression('/^PT-[A-F0-9]{24}$/', $code);
        }

        $this->assertCount(count($codes), array_unique($codes));
    }

    public function test_treatment_record_requires_same_patient_as_selected_appointment(): void
    {
        $this->actingAsClinicAdmin();

        $patient = $this->makePatient(['first_name' => 'Alpha']);
        $otherPatient = $this->makePatient(['first_name' => 'Bravo']);

        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'staff_id' => null,
            'appointment_date' => '2026-10-20',
            'appointment_time' => '09:00:00',
            'status' => 'scheduled',
        ]);

        $this->post(route('treatment-records.store'), [
            'patient_id' => $otherPatient->id,
            'appointment_id' => $appointment->id,
            'session_date' => '2026-10-20',
            'status' => 'completed',
        ])->assertSessionHasErrors(['patient_id']);
    }

    public function test_treatment_record_cannot_use_cancelled_or_missed_appointment(): void
    {
        $this->actingAsClinicAdmin();
        $patient = $this->makePatient();

        foreach (['cancelled', 'no_show'] as $status) {
            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'appointment_date' => '2026-10-20',
                'appointment_time' => $status === 'cancelled' ? '09:00:00' : '10:00:00',
                'status' => $status,
            ]);

            $this->post(route('treatment-records.store'), [
                'patient_id' => $patient->id,
                'appointment_id' => $appointment->id,
                'session_date' => '2026-10-20',
                'status' => 'completed',
            ])->assertSessionHasErrors(['appointment_id']);

            $this->assertSame($status, $appointment->fresh()->status);
        }

        $this->assertSame(0, TreatmentRecord::count());
    }

    public function test_only_one_treatment_record_can_be_linked_to_an_appointment(): void
    {
        $this->actingAsClinicAdmin();
        $patient = $this->makePatient();
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'appointment_date' => '2026-10-20',
            'appointment_time' => '09:00:00',
            'status' => 'scheduled',
        ]);
        $payload = [
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'session_date' => '2026-10-20',
            'status' => 'completed',
        ];

        $this->post(route('treatment-records.store'), $payload)->assertSessionDoesntHaveErrors();
        $this->post(route('treatment-records.store'), $payload)->assertSessionHasErrors(['appointment_id']);

        $this->assertSame(1, TreatmentRecord::where('appointment_id', $appointment->id)->count());
        $this->assertSame('completed', $appointment->fresh()->status);
    }

    public function test_in_progress_treatment_does_not_complete_appointment(): void
    {
        $this->actingAsClinicAdmin();
        $patient = $this->makePatient();
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'appointment_date' => '2026-10-20',
            'appointment_time' => '09:00:00',
            'status' => 'scheduled',
        ]);

        $this->post(route('treatment-records.store'), [
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'session_date' => '2026-10-20',
            'status' => 'in_progress',
        ])->assertSessionDoesntHaveErrors();

        $this->assertSame('scheduled', $appointment->fresh()->status);
    }

    public function test_treatment_completion_and_reopening_keep_appointment_status_in_sync(): void
    {
        $this->actingAsClinicAdmin();
        $patient = $this->makePatient();
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'appointment_date' => '2026-10-20',
            'appointment_time' => '09:00:00',
            'status' => 'scheduled',
        ]);
        $payload = [
            'patient_id' => $patient->id,
            'appointment_id' => $appointment->id,
            'session_date' => '2026-10-20',
            'status' => 'completed',
        ];

        $this->post(route('treatment-records.store'), $payload)->assertSessionDoesntHaveErrors();
        $record = TreatmentRecord::where('appointment_id', $appointment->id)->firstOrFail();
        $this->assertSame('completed', $appointment->fresh()->status);

        $this->put(route('treatment-records.update', $record), [
            ...$payload,
            'status' => 'in_progress',
        ])->assertSessionDoesntHaveErrors();

        $this->assertSame('in_progress', $record->fresh()->status);
        $this->assertSame('scheduled', $appointment->fresh()->status);
    }

    public function test_treatment_record_patient_cannot_be_changed_during_edit(): void
    {
        $this->actingAsClinicAdmin();
        $patient = $this->makePatient(['first_name' => 'Original']);
        $otherPatient = $this->makePatient(['first_name' => 'Different']);
        $record = TreatmentRecord::create([
            'patient_id' => $patient->id,
            'session_date' => '2026-10-20',
            'status' => 'completed',
        ]);

        $this->put(route('treatment-records.update', $record), [
            'patient_id' => $otherPatient->id,
            'session_date' => '2026-10-20',
            'status' => 'completed',
        ])->assertSessionHasErrors(['patient_id']);

        $this->assertSame($patient->id, $record->fresh()->patient_id);
    }

    public function test_treatment_records_cannot_be_permanently_deleted(): void
    {
        $this->actingAsClinicAdmin();
        $patient = $this->makePatient();
        $record = TreatmentRecord::create([
            'patient_id' => $patient->id,
            'session_date' => '2026-10-20',
            'status' => 'completed',
        ]);

        $this->delete('/treatment-records/'.$record->id)->assertMethodNotAllowed();

        $this->assertDatabaseHas('treatment_records', ['id' => $record->id]);
    }

    public function test_charge_requires_matching_patient_and_treatment_record(): void
    {
        $this->actingAsClinicAdmin();

        $patient = $this->makePatient(['first_name' => 'Alpha']);
        $otherPatient = $this->makePatient(['first_name' => 'Bravo']);

        $treatmentRecord = TreatmentRecord::create([
            'patient_id' => $patient->id,
            'session_date' => '2026-10-20',
            'status' => 'completed',
        ]);

        $this->post(route('charges.store'), [
            'patient_id' => $otherPatient->id,
            'treatment_record_id' => $treatmentRecord->id,
            'description' => 'Wrong patient charge',
            'amount' => 500,
            'charge_date' => '2026-10-20',
        ])->assertSessionHasErrors(['patient_id']);
    }

    public function test_charge_amount_must_fit_the_money_column_precision(): void
    {
        $this->actingAsClinicAdmin();
        $patient = $this->makePatient();

        foreach (['1.239', '100000000'] as $amount) {
            $this->post(route('charges.store'), [
                'patient_id' => $patient->id,
                'description' => 'Precision test',
                'amount' => $amount,
                'charge_date' => '2026-10-20',
            ])->assertSessionHasErrors(['amount']);
        }

        $this->assertSame(0, Charge::count());
    }

    public function test_charge_payment_and_waiver_transitions_are_one_way(): void
    {
        $this->actingAsClinicAdmin();
        $patient = $this->makePatient();
        $paidCharge = Charge::create([
            'patient_id' => $patient->id,
            'description' => 'Paid session',
            'amount' => 1500,
            'charge_date' => '2026-10-20',
            'status' => 'unpaid',
        ]);
        $waivedCharge = Charge::create([
            'patient_id' => $patient->id,
            'description' => 'Waived session',
            'amount' => 900,
            'charge_date' => '2026-10-21',
            'status' => 'unpaid',
        ]);

        $this->patch(route('charges.markPaid', $paidCharge))->assertRedirect();
        $this->patch(route('charges.waive', $waivedCharge))->assertRedirect();
        $this->assertSame('paid', $paidCharge->fresh()->status);
        $this->assertSame('waived', $waivedCharge->fresh()->status);

        $this->patch(route('charges.waive', $paidCharge))->assertSessionHasErrors(['charge']);
        $this->patch(route('charges.markPaid', $waivedCharge))->assertSessionHasErrors(['charge']);
        $this->assertSame('paid', $paidCharge->fresh()->status);
        $this->assertSame('waived', $waivedCharge->fresh()->status);
    }

    public function test_same_patient_can_have_multiple_appointments_on_same_day_when_times_differ(): void
    {
        $this->actingAsClinicAdmin();

        $patient = $this->makePatient();

        $first = $this->post(route('appointments.store'), [
            'patient_id' => $patient->id,
            'appointment_date' => '2026-10-20',
            'appointment_time' => '09:00',
            'status' => 'scheduled',
            'notes' => 'morning session',
        ]);

        $first->assertSessionDoesntHaveErrors();

        $second = $this->post(route('appointments.store'), [
            'patient_id' => $patient->id,
            'appointment_date' => '2026-10-20',
            'appointment_time' => '11:00',
            'status' => 'scheduled',
            'notes' => 'afternoon session',
        ]);

        $second->assertSessionDoesntHaveErrors();
        $this->assertEquals(2, Appointment::where('patient_id', $patient->id)->whereDate('appointment_date', '2026-10-20')->count());
    }

    public function test_same_patient_cannot_book_same_date_and_time_slot_twice(): void
    {
        $this->actingAsClinicAdmin();

        $patient = $this->makePatient();

        Appointment::create([
            'patient_id' => $patient->id,
            'appointment_date' => '2026-10-21',
            'appointment_time' => '14:30:00',
            'status' => 'scheduled',
        ]);

        $this->post(route('appointments.store'), [
            'patient_id' => $patient->id,
            'appointment_date' => '2026-10-21',
            'appointment_time' => '14:30',
            'status' => 'scheduled',
        ])->assertSessionHasErrors(['appointment_time']);
    }

    public function test_paid_or_waived_charges_cannot_be_modified_or_reopened(): void
    {
        $this->actingAsClinicAdmin();

        $patient = $this->makePatient();

        $charge = Charge::create([
            'patient_id' => $patient->id,
            'description' => 'Initial dialysis charge',
            'amount' => 1500,
            'charge_date' => '2026-10-20',
            'status' => 'paid',
        ]);

        $this->put(route('charges.update', $charge), [
            'patient_id' => $patient->id,
            'description' => 'Attempted tampering',
            'amount' => 2500,
            'charge_date' => '2026-10-20',
        ])->assertSessionHasErrors(['charge']);

        $this->patch(route('charges.markPaid', $charge))
            ->assertSessionHasErrors(['charge']);
    }

    public function test_app_timezone_is_manila_for_applications_using_configured_timezone(): void
    {
        $this->assertSame('Asia/Manila', config('app.timezone'));
    }
}
