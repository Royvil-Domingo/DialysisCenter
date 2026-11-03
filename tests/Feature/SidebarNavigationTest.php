<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sidebar_pages_load(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]));

        foreach ([
            '/dashboard',
            '/patients',
            '/patients/create',
            '/appointments',
            '/appointments/create',
            '/treatment-records',
            '/treatment-records/create',
            '/charges',
            '/charges/create',
            '/reports',
            '/reports/patients',
            '/reports/appointments',
            '/notifications',
            '/admin/users',
            '/admin/users/create',
            '/admin/settings',
            '/admin/activity-logs',
            '/admin/notifications/create',
        ] as $path) {
            $this->get($path)->assertOk();
        }
    }

    public function test_guests_are_redirected_from_staff_and_admin_pages(): void
    {
        $this->get('/patients')->assertRedirect('/login');
        $this->get('/admin/settings')->assertRedirect('/login');
    }

    public function test_staff_can_use_staff_pages_but_cannot_access_admin_pages(): void
    {
        $this->actingAs(User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]));

        $this->get('/patients')->assertOk();
        $this->get('/admin/settings')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
    }
}
