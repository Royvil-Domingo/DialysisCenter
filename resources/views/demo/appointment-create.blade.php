@extends('demo.layout')

@section('title', 'Demo Appointment Form')

@section('content')
<div class="page-head">
    <div>
        <h1>Read-only Demo</h1>
        <p>New appointment creation is disabled in public demo mode.</p>
    </div>
</div>
<div class="card-soft p-4">
    <p class="mb-0 text-muted">This preview intentionally blocks data changes. The form is unavailable and no records are persisted.</p>
</div>
@endsection
