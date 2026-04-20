@extends('layouts.app')

@section('title', 'User Management')
@section('page-title', 'User Management')
@section('page-subtitle', 'Manage staff accounts and role permissions')

@section('content')

{{-- Stats --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    <div class="kpi-card">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Total Staff</p>
        <p class="text-2xl font-display font-bold text-slate-800 mt-1">{{ $users->total() }}</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Admins</p>
        <p class="text-2xl font-display font-bold text-slate-800 mt-1">{{ $adminCount }}</p>
    </div>
    <div class="kpi-card">
        <p class="text-xs text-slate-400 font-semibold uppercase tracking-wide">Employees</p>
        <p class="text-2xl font-display font-bold text-slate-800 mt-1">{{ $empCount }}</p>
    </div>
    <div class="kpi-card {{ $pendingCount > 0 ? 'border-amber-200 bg-amber-50' : '' }}">
        <p class="text-xs {{ $pendingCount > 0 ? 'text-amber-500' : 'text-slate-400' }} font-semibold uppercase tracking-wide">
            Pending Role
        </p>
        <p class="text-2xl font-display font-bold {{ $pendingCount > 0 ? 'text-amber-600' : 'text-slate-800' }} mt-1">
            {{ $pendingCount }}
        </p>
        @if($pendingCount > 0)
        <p class="text-xs text-amber-500 mt-1">⚠ Needs role assignment</p>
        @endif
    </div>
</div>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100 flex items-center justify-between">
        <h2 class="font-display font-bold text-slate-800">All Staff Accounts</h2>
        <a href="{{ route('users.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add User
        </a>
    </div>

    <table class="fms-table w-full">
        <thead>
            <tr>
                <th class="text-left">User</th>
                <th class="text-left">Email</th>
                <th class="text-center">Role</th>
                <th class="text-center">Orders Processed</th>
                <th class="text-left">Joined</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white flex-shrink-0"
                             style="background:var(--accent)">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-semibold text-slate-700">{{ $user->name }}</p>
                            @if($user->id === auth()->id())
                                <p class="text-xs text-orange-400 font-medium">You</p>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="text-sm text-slate-500">{{ $user->email }}</td>
                <td class="text-center">
                    @php $roleName = $user->role?->name ?? 'none'; @endphp
                    @if($user->isPending())
                        <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-amber-100 text-amber-600">
                            ⚠ No Role
                        </span>
                    @else
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full capitalize
                        {{ $roleName === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-600' }}">
                        {{ ucfirst($roleName) }}
                    </span>
                    @endif
                </td>
                <td class="text-center font-semibold text-slate-700">{{ $user->orders_count }}</td>
                <td class="text-sm text-slate-400">{{ $user->created_at->format('M d, Y') }}</td>
                <td class="text-right">
                    <div class="flex items-center justify-end gap-3">
                        <a href="{{ route('users.edit', $user) }}"
                           class="text-xs text-slate-400 hover:text-slate-700 font-medium transition-colors">
                            Edit
                        </a>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('users.destroy', $user) }}"
                              onsubmit="return confirm('Remove {{ addslashes($user->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-600 transition-colors">
                                Delete
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center py-12 text-slate-400">
                    No users found.
                    <a href="{{ route('users.create') }}" class="text-orange-500 hover:underline ml-1">Create one →</a>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="px-5 py-4 border-t border-slate-100">
        {{ $users->links() }}
    </div>
</div>

@endsection