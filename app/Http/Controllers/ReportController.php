<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function patients(Request $request)
    {
        $patients = Patient::when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->withCount('appointments', 'treatmentRecords')
            ->orderBy('last_name')
            ->paginate(20)
            ->withQueryString();

        return view('reports.patients', compact('patients'));
    }

    public function appointments(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);
        $validator->after(function ($validator) use ($request) {
            if ($request->filled('from') && $request->filled('to') && $request->input('from') > $request->input('to')) {
                $validator->errors()->add('to', 'The end date must be on or after the start date.');
            }
        });
        $filters = $validator->validate();

        $from = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $to = $filters['to'] ?? now()->endOfMonth()->toDateString();

        $query = Appointment::whereBetween('appointment_date', [$from, $to]);
        $summary = (clone $query)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $appointments = $query->with('patient')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->paginate(50)
            ->withQueryString();

        return view('reports.appointments', compact('appointments', 'summary', 'from', 'to'));
    }
}
