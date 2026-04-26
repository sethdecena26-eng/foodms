<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('role')
                     ->withCount('orders')
                     ->latest()
                     ->paginate(20);

        $roles        = Role::all();
        $adminCount   = User::whereHas('role', fn($q) => $q->where('name', 'admin'))->count();
        $empCount     = User::whereHas('role', fn($q) => $q->where('name', 'employee'))->count();
        $pendingCount = User::whereNull('role_id')->count();

        return view('users.index', compact('users', 'roles', 'adminCount', 'empCount', 'pendingCount'));
    }

    public function create()
    {
        $roles = Role::all();
        return view('users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role_id'  => 'required|exists:roles,id',
        ]);

        User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'role_id'  => $data['role_id'],
        ]);

        return redirect()->route('users.index')
                         ->with('success', "User '{$data['name']}' created successfully.");
    }

    // ── Fixed: was missing entirely ───────────────────────────────────────────
    public function show(User $user)
    {
        $user->load('role');

        $orderStats = $user->orders()
            ->where('status', 'completed')
            ->selectRaw('COUNT(*) as total_orders,
                         SUM(total_amount) as total_revenue,
                         SUM(net_profit) as total_profit')
            ->first();

        $recentOrders = $user->orders()
            ->latest()
            ->limit(10)
            ->get();

        return view('users.show', compact('user', 'orderStats', 'recentOrders'));
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        return view('users.edit', compact('user', 'roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'role_id'  => 'required|exists:roles,id',
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
        ]);

        $user->update([
            'name'    => $data['name'],
            'email'   => $data['email'],
            'role_id' => $data['role_id'],
        ]);

        if (!empty($data['password'])) {
            $user->update(['password' => Hash::make($data['password'])]);
        }

        return redirect()->route('users.index')
                         ->with('success', "'{$user->name}' updated.");
    }

    public function destroy(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot archive your own account.');

        $name = $user->name;
        $user->delete();

        return redirect()->route('users.index')
                         ->with('success', "'{$name}' archived. You can restore them from Archives.");
    }
}