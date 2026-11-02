<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DemoModeTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_pages_render_without_database_access(): void
    {
        DB::listen(function () {
            throw new \RuntimeException('Demo route attempted a database query.');
        });

        $this->get('/demo')->assertOk()->assertSee('Demo Mode');
        $this->get('/demo/dashboard')->assertOk()->assertSee('Demo Dashboard');
        $this->get('/demo/patients')->assertOk()->assertSee('Demo Patients');
    }

    public function test_demo_routes_cannot_mutate_real_data(): void
    {
        $this->assertDatabaseCount('patients', 0);
        $this->assertDatabaseCount('appointments', 0);

        $this->from('/demo/patients')
            ->post('/demo/appointments', [
                'patient_id' => 999,
                'appointment_date' => '2026-10-20',
                'appointment_time' => '09:00',
                'status' => 'scheduled',
            ])
            ->assertRedirect('/demo/appointments');

        $this->assertDatabaseCount('patients', 0);
        $this->assertDatabaseCount('appointments', 0);
        $this->assertSame(0, Appointment::count());
        $this->assertSame(0, Patient::count());
    }
}
