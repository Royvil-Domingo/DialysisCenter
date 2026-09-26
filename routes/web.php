<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ChargeController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LabResultController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\My\AppointmentController as MyAppointmentController;
use App\Http\Controllers\My\LabResultController as MyLabResultController;
use App\Http\Controllers\My\MessageController as MyMessageController;
use App\Http\Controllers\My\SettingController as MySettingController;
use App\Http\Controllers\My\TreatmentHistoryController as MyTreatmentHistoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PatientRegisterController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\TreatmentRecordController;
use App\Http\Controllers\UserManagementController;
use Illuminate\Support\Facades\Route;

// ===== PUBLIC =====
Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/select-role', function () {
    return view('role-select');
})->name('role-select');

// Shared login for all 3 roles — one route, view branded by ?as=patient|doctor|admin
Auth::routes(['register' => false]);

// Patient self-registration only — doctor/admin accounts stay admin-created
Route::get('/patient/register', [PatientRegisterController::class, 'create'])->name('patient.register');
Route::post('/patient/register', [PatientRegisterController::class, 'store'])->name('patient.register.store');

Route::post('/chatbot/message', [ChatbotController::class, 'respond'])->name('chatbot.message');

// ===== AUTHENTICATED =====
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ---- Patient self-service ----
    Route::middleware('role:patient')->prefix('my')->name('my.')->group(function () {
        Route::get('/appointments', [MyAppointmentController::class, 'index'])->name('appointments');
        Route::get('/treatment-history', [MyTreatmentHistoryController::class, 'index'])->name('treatment-history');
        Route::get('/lab-results', [MyLabResultController::class, 'index'])->name('lab-results');
        Route::get('/messages', [MyMessageController::class, 'index'])->name('messages.index');
        Route::post('/messages', [MyMessageController::class, 'store'])->name('messages.store');
        Route::get('/settings', [MySettingController::class, 'edit'])->name('settings.edit');
        Route::put('/settings', [MySettingController::class, 'update'])->name('settings.update');
    });

    // ---- Doctor + Admin: patient/clinical management ----
    Route::middleware('role:admin,staff')->group(function () {
        Route::resource('patients', PatientController::class);
        Route::resource('appointments', AppointmentController::class);
        Route::resource('treatment-records', TreatmentRecordController::class);
        Route::resource('machines', MachineController::class)->except(['destroy']);
        Route::resource('lab-results', LabResultController::class)->except(['show']);

        Route::resource('charges', ChargeController::class)->except(['destroy']);
        Route::patch('charges/{charge}/mark-paid', [ChargeController::class, 'markPaid'])->name('charges.markPaid');
        Route::patch('charges/{charge}/waive', [ChargeController::class, 'waive'])->name('charges.waive');

        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/patients', [ReportController::class, 'patients'])->name('patients');
            Route::get('/appointments', [ReportController::class, 'appointments'])->name('appointments');
            Route::get('/machines', [ReportController::class, 'machines'])->name('machines');
            Route::get('/financial', [ReportController::class, 'financial'])->name('financial');
        });

        Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');

        Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
        Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');
    });

    // ---- Admin only ----
    Route::middleware('role:admin')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserManagementController::class)->except(['destroy', 'show']);
        Route::patch('users/{user}/toggle-active', [UserManagementController::class, 'toggleActive'])->name('users.toggleActive');

        Route::get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

        Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::get('notifications/create', [NotificationController::class, 'create'])->name('notifications.create');
        Route::post('notifications', [NotificationController::class, 'store'])->name('notifications.store');
    });
});