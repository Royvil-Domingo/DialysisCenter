<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]));
    }

    public function test_appointment_report_rejects_invalid_and_reversed_date_ranges(): void
    {
        $this->get('/reports/appointments?from=not-a-date&to=2026-10-31')
            ->assertSessionHasErrors(['from']);

        $this->get('/reports/appointments?from=2026-10-31&to=2026-10-01')
            ->assertSessionHasErrors(['to']);
    }

    public function test_appointment_report_paginates_rows_and_summarizes_the_full_range(): void
    {
        $patient = Patient::create([
            'first_name' => 'Report',
            'last_name' => 'Patient',
            'birthdate' => '2000-01-01',
            'sex' => 'female',
            'status' => 'active',
        ]);

        for ($index = 0; $index < 55; $index++) {
            Appointment::create([
                'patient_id' => $patient->id,
                'appointment_date' => '2026-10-20',
                'appointment_time' => sprintf('%02d:%02d:00', intdiv($index, 60), $index % 60),
                'status' => $index === 54 ? 'cancelled' : 'scheduled',
            ]);
        }

        $this->get('/reports/appointments?from=2026-10-01&to=2026-10-31')
            ->assertOk()
            ->assertViewHas('appointments', function (LengthAwarePaginator $appointments) {
                return $appointments->total() === 55
                    && $appointments->perPage() === 50
                    && $appointments->count() === 50;
            })
            ->assertViewHas('summary', function (Collection $summary) {
                return $summary->get('scheduled') === 54
                    && $summary->get('cancelled') === 1;
            });
    }
}
