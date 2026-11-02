<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\TreatmentRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TreatmentRecordController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $records = TreatmentRecord::with('patient')
            ->when($request->patient_id, fn ($q, $id) => $q->where('patient_id', $id))
            ->latest('session_date')
            ->paginate(15)
            ->withQueryString();

        return view('treatment-records.index', compact('records'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $patients = Patient::where('status', 'active')->orderBy('first_name')->get();
        $appointment = $request->appointment_id ? Appointment::find($request->appointment_id) : null;

        return view('treatment-records.create', compact('patients', 'appointment'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['recorded_by'] = $request->user()->id;

        $record = TreatmentRecord::create($validated);

        if ($record->appointment_id) {
            Appointment::where('id', $record->appointment_id)->update(['status' => 'completed']);
        }

        ActivityLog::record('create', 'treatment-records', "Recorded treatment session for patient #{$record->patient_id}");

        return redirect()->route('treatment-records.show', $record)->with('success', 'Treatment record saved.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TreatmentRecord $treatmentRecord)
    {
        $treatmentRecord->load('patient', 'appointment', 'charge');

        return view('treatment-records.show', ['record' => $treatmentRecord]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TreatmentRecord $treatmentRecord)
    {
        $patients = Patient::where('status', 'active')->orderBy('first_name')->get();

        return view('treatment-records.edit', ['record' => $treatmentRecord, 'patients' => $patients]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, TreatmentRecord $treatmentRecord)
    {
        $treatmentRecord->update($this->validated($request, $treatmentRecord));

        ActivityLog::record('update', 'treatment-records', "Updated treatment record #{$treatmentRecord->id}");

        return redirect()->route('treatment-records.show', $treatmentRecord)->with('success', 'Treatment record updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TreatmentRecord $treatmentRecord)
    {
        $treatmentRecord->delete();

        ActivityLog::record('delete', 'treatment-records', "Deleted treatment record #{$treatmentRecord->id}");

        return redirect()->route('treatment-records.index')->with('success', 'Treatment record deleted.');
    }

    protected function validated(Request $request, ?TreatmentRecord $record = null): array
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => [
                'required',
                'exists:patients,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $patient = Patient::find($value);

                    if ($patient && $patient->status !== 'active') {
                        $fail('Only active patients can have treatment records.');
                    }
                },
            ],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'session_date' => 'required|date',
            'pre_weight_kg' => 'nullable|numeric|min:0|max:999',
            'post_weight_kg' => 'nullable|numeric|min:0|max:999',
            'duration_minutes' => 'nullable|integer|min:0|max:600',
            'blood_pressure_pre' => 'nullable|string|max:20',
            'blood_pressure_post' => 'nullable|string|max:20',
            'dialyzer_used' => 'nullable|string|max:100',
            'complications' => 'nullable|string',
            'status' => 'nullable|in:in_progress,completed',
        ]);

        $validator->after(function ($validator) use ($request, $record) {
            $appointmentId = $request->input('appointment_id');
            $patientId = $request->input('patient_id');

            if (! $appointmentId || ! $patientId) {
                return;
            }

            $appointment = Appointment::find($appointmentId);
            if (! $appointment) {
                return;
            }

            if ((int) $appointment->patient_id !== (int) $patientId) {
                $validator->errors()->add('patient_id', 'The selected appointment belongs to a different patient.');
            }

            if ($record && $record->appointment_id && $record->appointment_id != $appointmentId && (int) $record->patient_id !== (int) $patientId) {
                $validator->errors()->add('patient_id', 'The treatment record cannot be reassigned to a different patient.');
            }
        });

        $validator->validate();

        return $validator->validated();
    }
}
