{{-- ================================================================
     resources/views/users/create.blade.php
     ================================================================ --}}
@extends('layouts.app')
@section('title', 'Add User')
@section('page-title', 'Add New User')
@section('page-subtitle', 'Create a staff account and assign a role')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-2xl border border-slate-100 p-6">
        <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="fms-label">Full Name *</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="fms-input" required placeholder="e.g., Maria Santos">
                @error('name') <p class="fms-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="fms-label">Email Address *</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       class="fms-input" required placeholder="maria@restaurant.com">
                @error('email') <p class="fms-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="fms-label">Role *</label>
                <select name="role_id" class="fms-input" required>
                    <option value="">— Select role —</option>
                    @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                        {{ $role->label }}
                    </option>
                    @endforeach
                </select>
                @error('role_id') <p class="fms-error">{{ $message }}</p> @enderror

                {{-- Role description --}}
                <div class="mt-2 p-3 bg-slate-50 rounded-lg text-xs text-slate-500 space-y-1">
                    <p><strong class="text-purple-600">Admin:</strong> Full access — all modules + user management.</p>
                    <p><strong class="text-blue-600">Employee:</strong> POS, inventory, and dashboard only.</p>
                </div>
            </div>

            <div>
                <label class="fms-label">Password *</label>
                <input type="password" name="password" class="fms-input" required>
                @error('password') <p class="fms-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="fms-label">Confirm Password *</label>
                <input type="password" name="password_confirmation" class="fms-input" required>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 justify-center">Create User</button>
                <a href="{{ route('users.index') }}" class="btn-ghost flex-1 text-center">Cancel</a>
            </div>
        </form>
    </div>
</div>

<style>
.fms-label { display:block; font-size:.7rem; font-weight:600; text-transform:uppercase; letter-spacing:.08em; color:#64748b; margin-bottom:.35rem; }
.fms-input { width:100%; padding:.55rem .75rem; border:1px solid #e2e8f0; border-radius:.5rem; font-size:.875rem; color:#1e293b; outline:none; transition:border-color .15s,box-shadow .15s; }
.fms-input:focus { border-color:#fb923c; box-shadow:0 0 0 3px rgba(249,115,22,.1); }
.fms-error { font-size:.75rem; color:#ef4444; margin-top:.25rem; }
</style>
@endsection