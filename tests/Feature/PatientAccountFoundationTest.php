<?php

namespace Tests\Feature;

use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientAccountFoundationTest extends TestCase
{
    use RefreshDatabase;

    private function makePatient(array $overrides = []): Patient
    {
        return Patient::create(array_merge([
            'first_name' => 'Avery',
            'last_name' => 'Patient',
            'birthdate' => '2000-01-01',
            'sex' => 'female',
            'status' => 'active',
        ], $overrides));
    }

    public function test_public_lookup_suggest_endpoint_no_longer_exists(): void
    {
        $this->makePatient();

        $this->getJson('/lookup/suggest?q=Av')->assertNotFound();
    }

    public function test_patient_role_can_be_stored_and_linked_one_to_one(): void
    {
        $patient = $this->makePatient();

        $user = User::factory()->create([
            'role' => 'patient',
            'patient_id' => $patient->id,
            'is_active' => true,
        ])->refresh();

        $this->assertTrue($user->isPatient());
        $this->assertFalse($user->isStaffMember());
        $this->assertFalse($user->must_change_password);
        $this->assertTrue($user->patient->is($patient));
        $this->assertTrue($patient->fresh()->user->is($user));
    }

    public function test_a_patient_record_can_only_have_one_portal_account(): void
    {
        $patient = $this->makePatient();

        User::factory()->create(['role' => 'patient', 'patient_id' => $patient->id, 'is_active' => true]);

        $this->expectException(QueryException::class);

        User::factory()->create(['role' => 'patient', 'patient_id' => $patient->id, 'is_active' => true]);
    }

    public function test_patient_account_for_an_inactive_patient_record_is_logged_out(): void
    {
        $patient = $this->makePatient(['status' => 'inactive']);

        $user = User::factory()->create([
            'role' => 'patient',
            'patient_id' => $patient->id,
            'is_active' => true,
        ]);

        $this->actingAs($user)->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_patient_account_without_a_linked_patient_record_is_logged_out(): void
    {
        $user = User::factory()->create([
            'role' => 'patient',
            'patient_id' => null,
            'is_active' => true,
        ]);

        $this->actingAs($user)->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }
}