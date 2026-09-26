<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = ActivityLog::with('user')
            ->when($request->module, fn ($q, $module) => $q->where('module', $module))
            ->when($request->user_id, fn ($q, $id) => $q->where('user_id', $id))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.activity-logs.index', compact('logs'));
    }
}
