<?php

use App\Http\Controllers\DemoController;
use Illuminate\Support\Facades\Route;

Route::prefix('demo')->name('demo.')->group(function () {
    Route::get('/', [DemoController::class, 'index'])->name('index');
    Route::get('/dashboard', [DemoController::class, 'dashboard'])->name('dashboard');
    Route::get('/patients', [DemoController::class, 'patients'])->name('patients');
    Route::get('/patients/{id}', [DemoController::class, 'patient'])->name('patient');
    Route::get('/appointments', [DemoController::class, 'appointments'])->name('appointments');
    Route::get('/appointments/create', [DemoController::class, 'createAppointment'])->name('appointments.create');
    Route::post('/appointments', [DemoController::class, 'storeAppointment'])->name('appointments.store');
    Route::get('/treatment-records', [DemoController::class, 'treatmentRecords'])->name('treatment-records');
    Route::get('/charges', [DemoController::class, 'charges'])->name('charges');
    Route::get('/reports', [DemoController::class, 'reports'])->name('reports');
    Route::get('/notifications', [DemoController::class, 'notifications'])->name('notifications');
});
