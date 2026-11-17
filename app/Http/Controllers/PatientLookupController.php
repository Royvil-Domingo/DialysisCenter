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

    public function suggest(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:60',
        ]);

        $query = trim((string) $request->input('q', ''));

        if ($query === '') {
            return response()->json(['results' => []]);
        }

        $patients = Patient::query()
            ->where(function ($q) use ($query) {
                $q->where('patient_code', 'like', "%{$query}%")
                    ->orWhere('first_name', 'like', "%{$query}%")
                    ->orWhere('last_name', 'like', "%{$query}%")
                    ->orWhereRaw('LOWER(CONCAT(first_name, " ", last_name)) LIKE ?', ['%' . strtolower($query) . '%']);
            })
            ->limit(6)
            ->get(['id', 'patient_code', 'first_name', 'last_name', 'birthdate']);

        return response()->json([
            'results' => $patients->map(function ($patient) {
                return [
                    'patient_code' => $patient->patient_code,
                    'label' => $patient->full_name . ' — ' . $patient->patient_code,
                    'birthdate' => $patient->birthdate?->format('Y-m-d'),
                ];
            })->values(),
        ]);
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'patient_code' => 'required|string|max:40',
            'birthdate' => 'required|date_format:Y-m-d',
        ]);

        $patient = Patient::where('patient_code', strtoupper(trim($validated['patient_code'])))
            ->whereDate('birthdate', $validated['birthdate'])
            ->first();

        if (! $patient) {
            return back()->withErrors(['patient_code' => 'No matching record found. Please check your reference code and birthdate.']);
        }

        // Only expose scheduling info here — never treatment/medical details on this public page.
        $appointments = $patient->appointments()
            ->where('appointment_date', '>=', now()->subDay())
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get(['id', 'appointment_date', 'appointment_time', 'status']);

        return view('lookup', [
            'patientName' => $patient->first_name.' '.substr($patient->last_name, 0, 1).'.',
            'appointments' => $appointments,
        ]);
    }
}