<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use Illuminate\Http\Request;

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
        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to = $request->to ?? now()->endOfMonth()->toDateString();

        $appointments = Appointment::with('patient')
            ->whereBetween('appointment_date', [$from, $to])
            ->orderBy('appointment_date')
            ->get();

        $summary = $appointments->groupBy('status')->map->count();

        return view('reports.appointments', compact('appointments', 'summary', 'from', 'to'));
    }
}
