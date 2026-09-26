<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ChargeController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientLookupController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TreatmentRecordController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// PUBLIC — no login required
Route::view('/', 'welcome')->name('welcome');
Route::get('/lookup', [PatientLookupController::class, 'index'])->name('lookup');
Route::post('/lookup/search', [PatientLookupController::class, 'search'])->name('lookup.search');
Route::post('/chatbot/message', [ChatbotController::class, 'respond'])->name('chatbot.message');

// register disabled — staff/admin accounts are created by an admin only, never self-signup
Auth::routes(['register' => false]);

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware(['role:admin,staff'])->group(function () {
        Route::resource('patients', PatientController::class);
        Route::resource('appointments', AppointmentController::class);
        Route::resource('treatment-records', TreatmentRecordController::class);

        // no show() in ChargeController — charges are viewed inline on the index list, not their own page
        Route::resource('charges', ChargeController::class)->except(['destroy', 'show']);
        Route::patch('/charges/{charge}/mark-paid', [ChargeController::class, 'markPaid'])->name('charges.markPaid');
        Route::patch('/charges/{charge}/waive', [ChargeController::class, 'waive'])->name('charges.waive');

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/patients', [ReportController::class, 'patients'])->name('patients');
            Route::get('/appointments', [ReportController::class, 'appointments'])->name('appointments');
        });

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    });

    Route::middleware(['role:admin'])->prefix('admin')->name('admin.')->group(function () {
        // no destroy()/show() in UserManagementController — deactivate via toggle-active instead of deleting accounts
        Route::resource('users', UserManagementController::class)->except(['destroy', 'show']);
        Route::patch('/users/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])->name('users.toggleActive');

        Route::get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update'); // PUT, not POST — the Blade form spoofs @method('PUT')

        // -> resources/views/admin/notifications/create.blade.php (Vince), not top-level notifications/
        Route::get('/notifications/create', [NotificationController::class, 'create'])->name('notifications.create');
        Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
    });
});