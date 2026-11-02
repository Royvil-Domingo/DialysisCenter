<?php

namespace App\Http\Controllers;

use App\Support\DemoData;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DemoController extends Controller
{
    public function index(): View
    {
        $clinic = DemoData::clinic();
        $stats = DemoData::stats();

        return view('demo.index', [
            'clinic' => $clinic,
            'stats' => $stats,
            'patients' => DemoData::patients(),
            'appointments' => DemoData::appointments(),
            'notifications' => DemoData::notifications(),
        ]);
    }

    public function dashboard(): View
    {
        $stats = DemoData::stats();

        return view('demo.dashboard', [
            'clinic' => DemoData::clinic(),
            'stats' => $stats,
            'patients' => DemoData::patients(),
            'appointments' => DemoData::appointments(),
            'notifications' => DemoData::notifications(),
        ]);
    }

    public function patients(): View
    {
        return view('demo.patients', [
            'clinic' => DemoData::clinic(),
            'patients' => DemoData::patients(),
        ]);
    }

    public function patient(string $id): View
    {
        $patient = DemoData::patientById($id);

        abort_if(! $patient, 404);

        return view('demo.patient', [
            'clinic' => DemoData::clinic(),
            'patient' => $patient,
            'appointments' => array_values(array_filter(DemoData::appointments(), fn ($appointment) => (string) $appointment['patient_id'] === (string) $id)),
            'charges' => array_values(array_filter(DemoData::charges(), fn ($charge) => (string) $charge['patient_id'] === (string) $id)),
        ]);
    }

    public function appointments(): View
    {
        return view('demo.appointments', [
            'clinic' => DemoData::clinic(),
            'appointments' => DemoData::appointments(),
            'patients' => DemoData::patients(),
        ]);
    }

    public function createAppointment(): View
    {
        return view('demo.appointment-create', [
            'clinic' => DemoData::clinic(),
            'patients' => DemoData::patients(),
        ]);
    }

    public function storeAppointment(Request $request): RedirectResponse
    {
        return redirect()->route('demo.appointments')->with('success', 'Demo mode is read-only. No real appointment data was changed.');
    }

    public function treatmentRecords(): View
    {
        return view('demo.treatment-records', [
            'clinic' => DemoData::clinic(),
            'records' => DemoData::treatmentRecords(),
            'patients' => DemoData::patients(),
        ]);
    }

    public function charges(): View
    {
        return view('demo.charges', [
            'clinic' => DemoData::clinic(),
            'charges' => DemoData::charges(),
            'patients' => DemoData::patients(),
        ]);
    }

    public function reports(): View
    {
        return view('demo.reports', [
            'clinic' => DemoData::clinic(),
            'stats' => DemoData::stats(),
            'patients' => DemoData::patients(),
            'appointments' => DemoData::appointments(),
        ]);
    }

    public function notifications(): View
    {
        return view('demo.notifications', [
            'clinic' => DemoData::clinic(),
            'notifications' => DemoData::notifications(),
        ]);
    }
}
