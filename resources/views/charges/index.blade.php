{{-- Owner: Vince | Data: $charges (paginated, with patient), $totals['unpaid'|'paid'] --}}
@extends('layouts.app')
@section('title', 'Billing')
@section('content')
<div class="page-head">
    <div><h1>Billing</h1><p>Record-keeping only. No online payment; staff mark charges paid or waived.</p></div>
    <a href="{{ route('charges.create') }}" class="btn btn-navy"><i class="bi bi-plus-lg"></i> Record Charge</a>
</div>
<div class="row g-3 mb-3">
    @include('partials.stat', ['icon' => 'bi-hourglass-split', 'label' => 'Unpaid Total', 'value' => '₱' . number_format($totals['unpaid'], 2)])
    @include('partials.stat', ['icon' => 'bi-check2-circle', 'label' => 'Paid Total', 'value' => '₱' . number_format($totals['paid'], 2)])
</div>
<div class="card-soft p-3 p-md-4">
    <form method="GET" class="row g-2 mb-3 no-print">
        <div class="col-md-4">
            <select name="status" class="form-select"><option value="">All statuses</option>
                @foreach (['unpaid', 'paid', 'waived'] as $s)<option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>@endforeach
            </select>
        </div>
        <div class="col-md-4 d-flex gap-2"><button class="btn btn-navy">Filter</button><a href="{{ route('charges.index') }}" class="btn btn-soft">Reset</a></div>
    </form>
    <div class="table-responsive">
        <table class="table table-soft align-middle mb-0">
            <thead><tr><th>Date</th><th>Patient</th><th>Description</th><th class="text-end">Amount</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            @forelse ($charges as $c)
                <tr>
                    <td>{{ $c->charge_date->format('M d, Y') }}</td>
                    <td class="fw-semibold">{{ $c->patient->full_name }}</td>
                    <td>{{ $c->description }}</td>
                    <td class="text-end">₱{{ number_format($c->amount, 2) }}</td>
                    <td>@include('partials.pill', ['status' => $c->status])</td>
                    <td class="text-end text-nowrap">
                        @if ($c->status === 'unpaid')
                            <form method="POST" action="{{ route('charges.markPaid', $c) }}" class="d-inline">@csrf @method('PATCH')<button class="btn btn-sm btn-navy">Mark Paid</button></form>
                            <form method="POST" action="{{ route('charges.waive', $c) }}" class="d-inline" onsubmit="return confirm('Waive this charge?')">@csrf @method('PATCH')<button class="btn btn-sm btn-soft">Waive</button></form>
                        @endif
                        <a href="{{ route('charges.edit', $c) }}" class="icon-btn"><i class="bi bi-pencil"></i></a>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="text-center text-muted py-4">No charges recorded.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    @include('partials.pagination', ['paginator' => $charges])
</div>
@endsection