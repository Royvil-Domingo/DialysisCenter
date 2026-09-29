{{-- Owner: Dren | Data: $users (paginated) --}}
@extends('layouts.app')
@section('title', 'Manage Users')
@section('content')
<div class="page-head">
    <div><h1>Manage Users</h1><p>Staff and administrator accounts</p></div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-navy"><i class="bi bi-plus-lg"></i> Add Account</a>
</div>
<div class="card-soft p-3 p-md-4">
    <form method="GET" class="row g-2 mb-3 no-print">
        <div class="col-md-4"><select name="role" class="form-select"><option value="">All roles</option>
            @foreach (['admin', 'staff'] as $r)<option value="{{ $r }}" @selected(request('role') === $r)>{{ ucfirst($r) }}</option>@endforeach</select></div>
        <div class="col-md-4"><button class="btn btn-navy">Filter</button></div>
    </form>
    <div class="table-responsive">
        <table class="table table-soft align-middle mb-0">
            <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Contact</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @foreach ($users as $u)
                <tr>
                    <td class="fw-semibold">{{ $u->name }}</td><td>{{ $u->email }}</td><td class="text-capitalize">{{ $u->role }}</td><td>{{ $u->contact_number ?: '—' }}</td>
                    <td>@include('partials.pill', ['status' => $u->is_active ? 'active' : 'inactive'])</td>
                    <td class="text-end text-nowrap">
                        <a href="{{ route('admin.users.edit', $u) }}" class="icon-btn"><i class="bi bi-pencil"></i></a>
                        @if ($u->id !== auth()->id())
                            <form method="POST" action="{{ route('admin.users.toggleActive', $u) }}" class="d-inline" onsubmit="return confirm('Change this account\'s status?')">
                                @csrf @method('PATCH')<button class="btn btn-sm btn-soft">{{ $u->is_active ? 'Deactivate' : 'Activate' }}</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    @include('partials.pagination', ['paginator' => $users])
</div>
@endsection