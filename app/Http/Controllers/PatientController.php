<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use app\Models\Patient;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $patients = Patient::query()
        ->when($request->search, function ($q, $search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('patient_code', 'like', "%{$search}%");
        })
        ->when($request->status, fn ($q, $status) => $q->where('status', $status))
        ->latest()
        ->paginate(15)
        ->withQueryString();

    return view('patients.index', compact('patients'));
}

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('patients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['registered_by'] = $request->user()->id;

        $patient = Patient::create($validated);

        ActivityLog::record('create', 'patients', "Registered patient {$patient->full_name}");

        return redirect()->route('patients.show', $patient)->with('success', 'Patient registered successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        $patient->load(['appointments' => fn ($q) => $q->latest('appointment_date')->take(10), 'charges' => fn ($q) => $q->latest()->take(10)]);

        return view('patients.show', compact('patient'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        return view('patients.edit', compact('patient'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient)
    {
        $patient->update($this->validated($request));

        ActivityLog::record('update', 'patients', "Updated patient {$patient->full_name}");

        return redirect()->route('patients.show', $patient)->with('success', 'Patient updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        $name = $patient->full_name;
        $patient->update(['status' => 'inactive']);

        ActivityLog::record('deactivate', 'patients', "Deactivated patient {$name}");

        return redirect()->route('patients.index')->with('success', 'Patient marked inactive.');
    }

    protected function validated(Request $request): array
    {
        return $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'birthdate' => 'required|date|before:today',
            'sex' => 'required|in:male,female',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'emergency_contact_name' => 'nullable|string|max:150',
            'emergency_contact_number' => 'nullable|string|max:20',
            'blood_type' => 'nullable|string|max:5',
            'dialysis_access_type' => 'nullable|string|max:100',
            'dry_weight_kg' => 'nullable|numeric|min:0|max:999',
            'allergies' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);
    }
}
