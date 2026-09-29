{{-- Owner: Cheyenne | $record (edit) / $appointment (create from appointment) may be set --}}
@php
    $r = $record ?? null;
    $ap = $appointment ?? null;
    $patientOpts = $patients->mapWithKeys(fn ($x) => [$x->id => $x->full_name . ' (' . $x->patient_code . ')']);
@endphp
<div class="row g-3">
    @include('partials.field', ['name' => 'patient_id', 'label' => 'Patient', 'type' => 'select', 'options' => $patientOpts, 'value' => $r?->patient_id ?? $ap?->patient_id, 'required' => true])
    @include('partials.field', ['name' => 'session_date', 'label' => 'Session Date', 'type' => 'date', 'value' => ($r?->session_date ?? $ap?->appointment_date ?? now())->format('Y-m-d'), 'required' => true])
    @include('partials.field', ['name' => 'pre_weight_kg', 'label' => 'Pre-dialysis Weight (kg)', 'type' => 'number', 'step' => '0.01', 'value' => $r?->pre_weight_kg])
    @include('partials.field', ['name' => 'post_weight_kg', 'label' => 'Post-dialysis Weight (kg)', 'type' => 'number', 'step' => '0.01', 'value' => $r?->post_weight_kg])
    @include('partials.field', ['name' => 'blood_pressure_pre', 'label' => 'BP Before (e.g. 120/80)', 'value' => $r?->blood_pressure_pre])
    @include('partials.field', ['name' => 'blood_pressure_post', 'label' => 'BP After', 'value' => $r?->blood_pressure_post])
    @include('partials.field', ['name' => 'duration_minutes', 'label' => 'Duration (minutes)', 'type' => 'number', 'value' => $r?->duration_minutes])
    @include('partials.field', ['name' => 'dialyzer_used', 'label' => 'Dialyzer Used', 'value' => $r?->dialyzer_used])
    @include('partials.field', ['name' => 'complications', 'label' => 'Complications / Notes', 'type' => 'textarea', 'value' => $r?->complications, 'col' => 'col-12'])
    @include('partials.field', ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'blank' => false, 'value' => $r?->status ?? 'completed', 'options' => ['completed' => 'Completed', 'in_progress' => 'In progress']])
</div>