@extends('demo.layout')

@section('title', 'Demo Billing')

@section('content')
<div class="page-head">
    <div>
        <h1>Demo Billing</h1>
        <p>Sample charge ledger for demonstration only.</p>
    </div>
</div>

<div class="row g-3 mb-3">
    <div class="col-sm-6 col-xl-3">
        <div class="card-soft p-3 d-flex align-items-center gap-3 h-100">
            <div class="stat-icon"><i class="bi bi-hourglass-split"></i></div>
            <div>
                <div class="text-muted small">Unpaid Total</div>
                <div class="stat-value">₱{{ number_format(array_sum(array_map(fn($charge) => $charge['status'] === 'unpaid' ? (float)$charge['amount'] : 0, $charges)), 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card-soft p-3 d-flex align-items-center gap-3 h-100">
            <div class="stat-icon"><i class="bi bi-check2-circle"></i></div>
            <div>
                <div class="text-muted small">Paid Total</div>
                <div class="stat-value">₱{{ number_format(array_sum(array_map(fn($charge) => $charge['status'] === 'paid' ? (float)$charge['amount'] : 0, $charges)), 2) }}</div>
            </div>
        </div>
    </div>
</div>

<div class="card-soft p-3 p-md-4">
    <div class="table-responsive">
        <table class="table table-soft align-middle mb-0">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Patient</th>
                    <th>Description</th>
                    <th class="text-end">Amount</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($charges as $charge)
                    @php $patient = collect($patients)->firstWhere('id', $charge['patient_id']); @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($charge['charge_date'])->format('M d, Y') }}</td>
                        <td class="fw-semibold">{{ $patient['first_name'] }} {{ $patient['last_name'] }}</td>
                        <td>{{ $charge['description'] }}</td>
                        <td class="text-end">₱{{ number_format($charge['amount'], 2) }}</td>
                        <td>@include('partials.pill', ['status' => $charge['status']])</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
