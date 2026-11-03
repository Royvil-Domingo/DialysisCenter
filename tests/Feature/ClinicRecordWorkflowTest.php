<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClinicRecordWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]));
    }

    public function test_staff_can_register_update_and_deactivate_a_patient(): void
    {
        $this->post(route('patients.store'), [
            'first_name' => 'Jamie',
            'last_name' => 'Rivera',
            'birthdate' => '1990-04-15',
            'sex' => 'female',
            'contact_number' => '09171234567',
            'status' => 'active',
        ])->assertRedirect();

        $patient = Patient::where('first_name', 'Jamie')->firstOrFail();
        $this->assertMatchesRegularExpression('/^PT-[A-F0-9]{24}$/', $patient->patient_code);
        $this->assertSame(auth()->id(), $patient->registered_by);

        $this->put(route('patients.update', $patient), [
            'first_name' => 'Jamie',
            'last_name' => 'Rivera-Santos',
            'birthdate' => '1990-04-15',
            'sex' => 'female',
            'contact_number' => '09171234567',
            'status' => 'active',
        ])->assertRedirect();

        $this->delete(route('patients.destroy', $patient))->assertRedirect();
        $this->assertSame('inactive', $patient->fresh()->status);
        $this->assertSame('Rivera-Santos', $patient->fresh()->last_name);
    }

    public function test_staff_can_schedule_update_and_cancel_an_appointment(): void
    {
        $patient = Patient::create([
            'first_name' => 'Taylor',
            'last_name' => 'Patient',
            'birthdate' => '1992-03-10',
            'sex' => 'male',
            'status' => 'active',
        ]);

        $this->post(route('appointments.store'), [
            'patient_id' => $patient->id,
            'appointment_date' => '2026-11-10',
            'appointment_time' => '09:00',
            'status' => 'scheduled',
            'notes' => 'Initial schedule',
        ])->assertRedirect();

        $appointment = Appointment::firstOrFail();
        $this->put(route('appointments.update', $appointment), [
            'patient_id' => $patient->id,
            'appointment_date' => '2026-11-11',
            'appointment_time' => '10:30',
            'status' => 'scheduled',
            'notes' => 'Updated schedule',
        ])->assertRedirect();

        $this->assertSame('Updated schedule', $appointment->fresh()->notes);

        $this->delete(route('appointments.destroy', $appointment))->assertRedirect();
        $this->assertSame('cancelled', $appointment->fresh()->status);
    }

    public function test_inactive_patient_cannot_be_scheduled(): void
    {
        $patient = Patient::create([
            'first_name' => 'Inactive',
            'last_name' => 'Patient',
            'birthdate' => '1992-03-10',
            'sex' => 'male',
            'status' => 'inactive',
        ]);

        $this->post(route('appointments.store'), [
            'patient_id' => $patient->id,
            'appointment_date' => '2026-11-10',
            'appointment_time' => '09:00',
            'status' => 'scheduled',
        ])->assertSessionHasErrors(['patient_id']);

        $this->assertSame(0, Appointment::count());
    }
}
