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
