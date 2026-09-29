{{-- Owner: Rian | Data: $settings (clinic_name, clinic_address, clinic_contact_number, clinic_email, operating_hours) --}}
@extends('layouts.app')
@section('title', 'Clinic Settings')
@section('content')
<div class="page-head"><div><h1>Clinic Settings</h1><p>Shown on the public home page</p></div></div>
<form method="POST" action="{{ route('admin.settings.update') }}" class="card-soft p-4" style="max-width:820px">
    @csrf @method('PUT')
    <div class="row g-3">
        @include('partials.field', ['name' => 'clinic_name', 'label' => 'Clinic Name', 'value' => $settings['clinic_name'], 'col' => 'col-12'])
        @include('partials.field', ['name' => 'clinic_address', 'label' => 'Address', 'value' => $settings['clinic_address'], 'col' => 'col-12'])
        @include('partials.field', ['name' => 'clinic_contact_number', 'label' => 'Phone Number', 'value' => $settings['clinic_contact_number']])
        @include('partials.field', ['name' => 'clinic_email', 'label' => 'Email', 'type' => 'email', 'value' => $settings['clinic_email']])
        @include('partials.field', ['name' => 'operating_hours', 'label' => 'Operating Hours', 'value' => $settings['operating_hours'], 'col' => 'col-12'])
    </div>
    <div class="text-end mt-4"><button type="submit" class="btn btn-navy">Save Changes</button></div>
</form>
@endsection