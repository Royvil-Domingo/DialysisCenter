<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_admin_can_login(): void
    {
        putenv('ADMIN_EMAIL=admin@dialysiscenter.test');
        putenv('ADMIN_PASSWORD=Admin@12345');

        $this->seed(AdminSeeder::class);

        $this->post('/login', [
            'email' => 'admin@dialysiscenter.test',
            'password' => 'Admin@12345',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs(
            User::where('email', 'admin@dialysiscenter.test')->firstOrFail()
        );
    }

    public function test_inactive_user_cannot_request_a_password_reset(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive@example.com',
            'is_active' => false,
        ]);

        $this->from('/password/reset')->post('/password/email', ['email' => $user->email])
            ->assertSessionHasErrors('email');
    }

    public function test_inactive_user_cannot_complete_a_password_reset(): void
    {
        $user = User::factory()->create([
            'email' => 'inactive-reset@example.com',
            'is_active' => false,
        ]);

        $token = Password::createToken($user);

        $this->from('/password/reset')->post('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ])->assertSessionHasErrors('email');
    }

    public function test_inactive_users_are_logged_out_from_existing_sessions(): void
    {
        $user = User::factory()->create([
            'email' => 'active@example.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk();

        $user->update(['is_active' => false]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/login');
    }

    public function test_admin_cannot_deactivate_or_demote_their_own_account(): void
    {
        $admin = User::factory()->create([
            'email' => 'selfprotect@example.com',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.users.toggleActive', $admin))
            ->assertSessionHasErrors('user');

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role' => 'staff',
                'contact_number' => $admin->contact_number,
            ])->assertSessionHasErrors('role');
    }
}
