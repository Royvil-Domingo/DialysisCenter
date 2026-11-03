<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);
        $this->actingAs($this->admin);
    }

    public function test_admin_can_create_staff_account_with_a_hashed_password_and_deactivate_it(): void
    {
        $this->post(route('admin.users.store'), [
            'name' => 'Clinic Staff',
            'email' => 'clinic.staff@example.test',
            'password' => 'StrongPassword123!',
            'password_confirmation' => 'StrongPassword123!',
            'role' => 'staff',
            'contact_number' => '09171234567',
        ])->assertRedirect(route('admin.users.index'));

        $staff = User::where('email', 'clinic.staff@example.test')->firstOrFail();
        $this->assertSame('staff', $staff->role);
        $this->assertTrue(Hash::check('StrongPassword123!', $staff->password));
        $this->assertNotSame('StrongPassword123!', $staff->password);

        $this->patch(route('admin.users.toggleActive', $staff))->assertRedirect();
        $this->assertFalse($staff->fresh()->is_active);
    }

    public function test_admin_can_update_clinic_settings_and_invalid_email_is_rejected(): void
    {
        $this->put(route('admin.settings.update'), [
            'clinic_name' => 'Northside Dialysis Center',
            'clinic_address' => '12 Clinic Road',
            'clinic_contact_number' => '09171234567',
            'clinic_email' => 'contact@example.test',
            'operating_hours' => 'Monday to Saturday, 6 AM to 6 PM',
        ])->assertRedirect();

        $this->assertSame('Northside Dialysis Center', Setting::get('clinic_name'));

        $this->from(route('admin.settings.edit'))
            ->put(route('admin.settings.update'), ['clinic_email' => 'not-an-email'])
            ->assertSessionHasErrors(['clinic_email']);
    }

    public function test_staff_only_sees_all_staff_notifications(): void
    {
        Notification::create([
            'title' => 'Staff update',
            'message' => 'All staff can see this.',
            'audience' => 'all_staff',
            'posted_by' => $this->admin->id,
        ]);
        Notification::create([
            'title' => 'Admin update',
            'message' => 'Only administrators can see this.',
            'audience' => 'admin_only',
            'posted_by' => $this->admin->id,
        ]);

        $this->actingAs(User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]))->get('/notifications')
            ->assertOk()
            ->assertSee('Staff update')
            ->assertDontSee('Admin update');
    }

    public function test_staff_cannot_post_administrator_notifications(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);

        $this->actingAs($staff)
            ->post('/admin/notifications', [
                'title' => 'Unauthorized announcement',
                'message' => 'Should not be stored.',
                'audience' => 'all_staff',
            ])->assertForbidden();

        $this->assertDatabaseMissing('notifications', ['title' => 'Unauthorized announcement']);
    }

    public function test_notification_posting_is_validated_and_audited(): void
    {
        $this->post(route('admin.notifications.store'), [
            'title' => 'Clinic announcement',
            'message' => 'Please review the updated schedule.',
            'audience' => 'all_staff',
            'expires_at' => now()->addDay()->toDateTimeString(),
        ])->assertRedirect(route('notifications.index'));

        $this->assertDatabaseHas('notifications', [
            'title' => 'Clinic announcement',
            'audience' => 'all_staff',
            'posted_by' => $this->admin->id,
        ]);
        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $this->admin->id,
            'action' => 'create',
            'module' => 'notifications',
        ]);

        $this->from(route('admin.notifications.create'))
            ->post(route('admin.notifications.store'), [
                'title' => 'Invalid announcement',
                'message' => 'Message',
                'audience' => 'everyone',
            ])->assertSessionHasErrors(['audience']);
    }
}
