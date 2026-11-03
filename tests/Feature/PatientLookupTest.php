<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\TreatmentRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientLookupTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_check_schedule_with_reference_code_and_birthdate_without_exposing_clinical_data(): void
    {
        $patient = Patient::create([
            'first_name' => 'Avery',
            'last_name' => 'Patient',
            'birthdate' => '2000-01-01',
            'sex' => 'female',
            'status' => 'active',
            'blood_type' => 'O+',
        ]);
        $appointment = Appointment::create([
            'patient_id' => $patient->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '09:30:00',
            'status' => 'scheduled',
        ]);
        TreatmentRecord::create([
            'patient_id' => $patient->id,
            'session_date' => now()->toDateString(),
            'complications' => 'Private clinical note sentinel',
            'status' => 'completed',
        ]);

        $this->post('/lookup/search', [
            'patient_code' => strtolower(' '.$patient->patient_code.' '),
            'birthdate' => '2000-01-01',
        ])->assertOk()
            ->assertSee('Hello, Avery P.')
            ->assertSee($appointment->appointment_date->format('D, M d, Y'))
            ->assertDontSee('O+')
            ->assertDontSee('Private clinical note sentinel');
    }

    public function test_lookup_rejects_mismatched_birthdate_with_a_generic_error(): void
    {
        $patient = Patient::create([
            'first_name' => 'Avery',
            'last_name' => 'Patient',
            'birthdate' => '2000-01-01',
            'sex' => 'female',
            'status' => 'active',
        ]);

        $this->from('/lookup')->post('/lookup/search', [
            'patient_code' => $patient->patient_code,
            'birthdate' => '2001-01-01',
        ])->assertRedirect('/lookup')
            ->assertSessionHasErrors(['patient_code']);
    }

    public function test_lookup_rejects_malformed_dates_and_oversized_reference_codes(): void
    {
        $this->from('/lookup')->post('/lookup/search', [
            'patient_code' => str_repeat('A', 41),
            'birthdate' => 'January 1, 2000',
        ])->assertRedirect('/lookup')
            ->assertSessionHasErrors(['patient_code', 'birthdate']);
    }

    public function test_lookup_throttle_cannot_be_bypassed_by_rotating_forwarded_ips(): void
    {
        $payload = [
            'patient_code' => 'PT-'.str_repeat('A', 24),
            'birthdate' => '2000-01-01',
        ];

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->post('/lookup/search', $payload, [
                'X-Forwarded-For' => '198.51.100.'.$attempt,
            ])->assertRedirect();
        }

        $this->post('/lookup/search', $payload, [
            'X-Forwarded-For' => '198.51.100.6',
        ])->assertTooManyRequests();
    }
}
