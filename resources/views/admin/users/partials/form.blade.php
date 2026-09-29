{{-- Owner: Dren | $user is set when editing (blank password = keep current) --}}
@php $u = $user ?? null; @endphp
<div class="row g-3">
    @include('partials.field', ['name' => 'name', 'label' => 'Full Name', 'value' => $u?->name, 'required' => true])
    @include('partials.field', ['name' => 'email', 'label' => 'Email', 'type' => 'email', 'value' => $u?->email, 'required' => true])
    @include('partials.field', ['name' => 'role', 'label' => 'Role', 'type' => 'select', 'blank' => false, 'value' => $u?->role ?? 'staff', 'options' => ['staff' => 'Staff', 'admin' => 'Administrator']])
    @include('partials.field', ['name' => 'contact_number', 'label' => 'Contact Number', 'value' => $u?->contact_number])
    @include('partials.field', ['name' => 'password', 'label' => $u ? 'New Password (leave blank to keep)' : 'Password', 'type' => 'password', 'required' => ! $u])
    @include('partials.field', ['name' => 'password_confirmation', 'label' => 'Confirm Password', 'type' => 'password', 'required' => ! $u])
</div>