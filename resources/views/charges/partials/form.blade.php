{{-- Owner: Vince | $charge (edit), $treatmentRecords (create only) --}}
@php
    $c = $charge ?? null;
    $patientOpts = $patients->mapWithKeys(fn ($x) => [$x->id => $x->full_name . ' (' . $x->patient_code . ')']);
@endphp
<div class="row g-3">
    @include('partials.field', ['name' => 'patient_id', 'label' => 'Patient', 'type' => 'select', 'options' => $patientOpts, 'value' => $c?->patient_id, 'required' => true, 'col' => 'col-12'])
    @isset($treatmentRecords)
        @include('partials.field', ['name' => 'treatment_record_id', 'label' => 'Linked Treatment Session (optional)', 'type' => 'select', 'col' => 'col-12', 'value' => $c?->treatment_record_id,
            'options' => $treatmentRecords->mapWithKeys(fn ($t) => [$t->id => $t->patient->full_name . ' — ' . $t->session_date->format('M d, Y')])])
    @endisset
    @include('partials.field', ['name' => 'description', 'label' => 'Description', 'value' => $c?->description, 'required' => true, 'col' => 'col-12'])
    @include('partials.field', ['name' => 'amount', 'label' => 'Amount (₱)', 'type' => 'number', 'step' => '0.01', 'value' => $c?->amount, 'required' => true])
    @include('partials.field', ['name' => 'charge_date', 'label' => 'Charge Date', 'type' => 'date', 'value' => ($c?->charge_date ?? now())->format('Y-m-d'), 'required' => true])
</div>