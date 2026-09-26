<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Charge;
use App\Models\Notification;
use App\Models\Patient;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $data = [
            'totalPatients' => Patient::where('status', 'active')->count(),
            'todaysAppointments' => Appointment::whereDate('appointment_date', today())
                ->orderBy('appointment_time')
                ->with('patient')
                ->get(),
            'unpaidChargesTotal' => Charge::where('status', 'unpaid')->sum('amount'),
            'unpaidChargesCount' => Charge::where('status', 'unpaid')->count(),
            'notifications' => Notification::active()
                ->where(function ($q) use ($user) {
                    $q->where('audience', 'all_staff');
                    if ($user->isAdmin()) {
                        $q->orWhere('audience', 'admin_only');
                    }
                })
                ->latest()
                ->take(5)
                ->get(),
        ];

        return view('dashboard', $data);
    }
}
