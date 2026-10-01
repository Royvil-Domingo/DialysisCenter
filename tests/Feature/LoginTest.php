<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_admin_can_login(): void
    {
        $this->seed(AdminSeeder::class);

        $this->post('/login', [
            'email' => 'admin@dialysiscenter.test',
            'password' => 'Admin@12345',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs(
            User::where('email', 'admin@dialysiscenter.test')->firstOrFail()
        );
    }
}
