<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $users = User::when($request->role, fn ($q, $role) => $q->where('role', $role))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,staff',
            'contact_number' => 'nullable|string|max:20',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        ActivityLog::record('create', 'users', "Created {$user->role} account for {$user->name}");

        return redirect()->route('admin.users.index')->with('success', 'Account created.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $currentUser = $request->user();

        if ($currentUser && $currentUser->is($user) && $validatedRole = $request->input('role')) {
            if ($validatedRole !== 'admin') {
                return back()->withErrors([
                    'role' => 'You cannot remove your own administrator role.',
                ]);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|in:admin,staff',
            'contact_number' => 'nullable|string|max:20',
        ]);

        if ($currentUser && $currentUser->is($user) && $validated['role'] !== 'admin') {
            return back()->withErrors([
                'role' => 'You cannot remove your own administrator role.',
            ]);
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        ActivityLog::record('update', 'users', "Updated account for {$user->name}");

        return redirect()->route('admin.users.index')->with('success', 'Account updated.');
    }

    public function toggleActive(Request $request, User $user)
    {
        $currentUser = $request->user();

        if ($currentUser && $currentUser->is($user)) {
            return back()->withErrors([
                'user' => 'You cannot deactivate your own active account.',
            ]);
        }

        $user->update(['is_active' => !$user->is_active]);

        ActivityLog::record('toggle-active', 'users', "Set {$user->name} to " . ($user->is_active ? 'active' : 'inactive'));

        return back()->with('success', 'Account status updated.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
