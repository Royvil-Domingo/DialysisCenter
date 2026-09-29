{{-- Owner: Dren | $appointment is set when editing --}}
@php
    $a = $appointment ?? null;
    $patientOpts = $patients->mapWithKeys(fn ($x) => [$x->id => $x->full_name . ' (' . $x->patient_code . ')']);
@endphp
<div class="row g-3">
    @include('partials.field', ['name' => 'patient_id', 'label' => 'Patient', 'type' => 'select', 'options' => $patientOpts, 'value' => $a?->patient_id, 'required' => true, 'col' => 'col-12'])
    @include('partials.field', ['name' => 'appointment_date', 'label' => 'Date', 'type' => 'date', 'value' => $a?->appointment_date?->format('Y-m-d'), 'required' => true])
    @include('partials.field', ['name' => 'appointment_time', 'label' => 'Time', 'type' => 'time', 'value' => $a ? substr($a->appointment_time, 0, 5) : null, 'required' => true])
    @include('partials.field', ['name' => 'staff_id', 'label' => 'Assigned Staff', 'type' => 'select', 'options' => $staff->pluck('name', 'id'), 'value' => $a?->staff_id])
    @if ($a)
        @include('partials.field', ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'blank' => false, 'value' => $a->status,
            'options' => ['scheduled' => 'Scheduled', 'completed' => 'Completed', 'cancelled' => 'Cancelled', 'no_show' => 'No show']])
    @endif
    @include('partials.field', ['name' => 'notes', 'label' => 'Notes', 'type' => 'textarea', 'value' => $a?->notes, 'col' => 'col-12'])
</div>