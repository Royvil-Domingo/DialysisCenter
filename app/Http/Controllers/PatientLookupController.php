<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\Request;

class PatientLookupController extends Controller
{
    public function index()
    {
        return view('lookup');
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'patient_code' => 'required|string',
            'birthdate' => 'required|date',
        ]);

        $patient = Patient::where('patient_code', $validated['patient_code'])
            ->whereDate('birthdate', $validated['birthdate'])
            ->first();

        if (!$patient) {
            return back()->withErrors(['patient_code' => 'No matching record found. Please check your reference code and birthdate.']);
        }

        // Only expose scheduling info here — never treatment/medical details on this public page.
        $appointments = $patient->appointments()
            ->where('appointment_date', '>=', now()->subDay())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get(['id', 'appointment_date', 'appointment_time', 'status']);

        return view('lookup', [
            'patientName' => $patient->first_name . ' ' . substr($patient->last_name, 0, 1) . '.',
            'appointments' => $appointments,
        ]);
    }
}
