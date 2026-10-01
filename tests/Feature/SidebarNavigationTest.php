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

        $this->get('/patients')->assertStatus(200);
        $this->get('/appointments')->assertStatus(200);
        $this->get('/admin/settings')->assertStatus(200);
    }
}
