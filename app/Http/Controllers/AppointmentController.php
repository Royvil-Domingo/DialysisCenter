<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $appointments = Appointment::query()
            ->with('patient', 'staff')
            ->when($request->date, fn ($q, $date) => $q->whereDate('appointment_date', $date))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(15)
            ->withQueryString();

        return view('appointments.index', compact('appointments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $patients = Patient::where('status', 'active')->orderBy('first_name')->get();
        $staff = User::where('is_active', true)->orderBy('name')->get();

        return view('appointments.create', compact('patients', 'staff'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $this->validated($request);
        $validated['created_by'] = $request->user()->id;

        $appointment = Appointment::create($validated);

        ActivityLog::record('create', 'appointments', "Scheduled appointment #{$appointment->id} for patient #{$appointment->patient_id}");

        return redirect()->route('appointments.show', $appointment)->with('success', 'Appointment scheduled.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        $appointment->load('patient', 'staff', 'treatmentRecord');

        return view('appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment)
    {
        $patients = Patient::where('status', 'active')->orderBy('first_name')->get();
        $staff = User::where('is_active', true)->orderBy('name')->get();

        return view('appointments.edit', compact('appointment', 'patients', 'staff'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Appointment $appointment)
    {
        $appointment->update($this->validated($request, $appointment));

        ActivityLog::record('update', 'appointments', "Updated appointment #{$appointment->id}");

        return redirect()->route('appointments.show', $appointment)->with('success', 'Appointment updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->update(['status' => 'cancelled']);

        ActivityLog::record('cancel', 'appointments', "Cancelled appointment #{$appointment->id}");

        return redirect()->route('appointments.index')->with('success', 'Appointment cancelled.');
    }

    protected function validated(Request $request, ?Appointment $appointment = null): array
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => [
                'required',
                'exists:patients,id',
                function (string $attribute, mixed $value, \Closure $fail) {
                    $patient = Patient::find($value);

                    if ($patient && $patient->status !== 'active') {
                        $fail('Only active patients can be scheduled.');
                    }
                },
            ],
            'staff_id' => 'nullable|exists:users,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
            'status' => 'nullable|in:scheduled,completed,cancelled,no_show',
            'notes' => 'nullable|string',
        ]);

        $validator->after(function ($validator) use ($request, $appointment) {
            $patientId = $request->input('patient_id');
            $appointmentDate = $request->input('appointment_date');
            $appointmentTime = $request->input('appointment_time');

            if (! $patientId || ! $appointmentDate || ! $appointmentTime) {
                return;
            }

            $normalizedTime = date('H:i:s', strtotime($appointmentTime));

            $query = Appointment::query()
                ->where('patient_id', $patientId)
                ->whereDate('appointment_date', $appointmentDate)
                ->whereRaw('TIME(appointment_time) = ?', [$normalizedTime])
                ->whereIn('status', ['scheduled', 'completed']);

            if ($appointment) {
                $query->whereKeyNot($appointment->id);
            }

            if ($query->exists()) {
                $validator->errors()->add('appointment_time', 'This patient already has an appointment scheduled at the same date and time.');
            }

            $selectedAppointmentId = $request->input('existing_appointment_id');
            $linkedAppointment = $request->filled('appointment_id') ? Appointment::find($request->input('appointment_id')) : null;
            if ($linkedAppointment && $linkedAppointment->patient_id != $patientId) {
                $validator->errors()->add('patient_id', 'The selected appointment belongs to a different patient.');
            }
        });

        $validator->validate();

        return $validator->validated();
    }
}
