{{-- Owner: Rian | $patient is set when editing --}}
@php
    $p = $patient ?? null;
    $bt = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
    $access = ['AV Fistula', 'AV Graft', 'Tunneled Catheter', 'Temporary Catheter'];
@endphp
<div class="row g-3">
    @include('partials.field', ['name' => 'first_name', 'label' => 'First Name', 'value' => $p?->first_name, 'required' => true])
    @include('partials.field', ['name' => 'last_name', 'label' => 'Last Name', 'value' => $p?->last_name, 'required' => true])
    @include('partials.field', ['name' => 'birthdate', 'label' => 'Birthdate', 'type' => 'date', 'value' => $p?->birthdate?->format('Y-m-d'), 'required' => true])
    @include('partials.field', ['name' => 'sex', 'label' => 'Sex', 'type' => 'select', 'options' => ['male' => 'Male', 'female' => 'Female'], 'value' => $p?->sex, 'required' => true])
    @include('partials.field', ['name' => 'contact_number', 'label' => 'Contact Number', 'value' => $p?->contact_number])
    @include('partials.field', ['name' => 'blood_type', 'label' => 'Blood Type', 'type' => 'select', 'options' => array_combine($bt, $bt), 'value' => $p?->blood_type])
    @include('partials.field', ['name' => 'address', 'label' => 'Full Address', 'type' => 'textarea', 'value' => $p?->address, 'col' => 'col-12'])
    @include('partials.field', ['name' => 'emergency_contact_name', 'label' => 'Emergency Contact Name', 'value' => $p?->emergency_contact_name])
    @include('partials.field', ['name' => 'emergency_contact_number', 'label' => 'Emergency Contact Number', 'value' => $p?->emergency_contact_number])
    @include('partials.field', ['name' => 'dialysis_access_type', 'label' => 'Dialysis Access', 'type' => 'select', 'options' => array_combine($access, $access), 'value' => $p?->dialysis_access_type])
    @include('partials.field', ['name' => 'dry_weight_kg', 'label' => 'Dry Weight (kg)', 'type' => 'number', 'step' => '0.01', 'value' => $p?->dry_weight_kg])
    @include('partials.field', ['name' => 'allergies', 'label' => 'Allergies', 'type' => 'textarea', 'value' => $p?->allergies, 'col' => 'col-12'])
    @include('partials.field', ['name' => 'status', 'label' => 'Status', 'type' => 'select', 'options' => ['active' => 'Active', 'inactive' => 'Inactive'], 'blank' => false, 'value' => $p?->status ?? 'active'])
</div>