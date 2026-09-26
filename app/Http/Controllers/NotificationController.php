<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = Notification::active()
            ->where(function ($q) use ($user) {
                $q->where('audience', 'all_staff');
                if ($user->isAdmin()) {
                    $q->orWhere('audience', 'admin_only');
                }
            })
            ->latest()
            ->paginate(15);

        return view('notifications.index', compact('notifications'));
    }

    public function create()
    {
        return view('admin.notifications.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:150',
            'message' => 'required|string',
            'audience' => 'required|in:all_staff,admin_only',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $validated['posted_by'] = $request->user()->id;

        Notification::create($validated);

        return redirect()->route('notifications.index')->with('success', 'Notification posted.');
    }
}
