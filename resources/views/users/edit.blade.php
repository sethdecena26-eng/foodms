@extends('layouts.app')
@section('title', 'Edit User')
@section('page-title', 'Edit: ' . $user->name)
@section('page-subtitle', 'Update account details and role')

@section('content')
<div class="max-w-lg">
    <div class="bg-white rounded-2xl border border-slate-100 p-6">
        <form method="POST" action="{{ route('users.update', $user) }}" class="space-y-4">
            @csrf @method('PUT')

            <div>
                <label class="fms-label">Full Name *</label>
                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                       class="fms-input" required>
                @error('name') <p class="fms-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="fms-label">Email Address *</label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                       class="fms-input" required>
                @error('email') <p class="fms-error">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="fms-label">Role *</label>
                <select name="role_id" class="fms-input" required>
                    @foreach($roles as $role)
                    <option value="{{ $role->id }}" {{ (old('role_id', $user->role_id) == $role->id) ? 'selected' : '' }}>
                        {{ $role->label }}
                    </option>
                    @endforeach
                </select>
                @error('role_id') <p class="fms-error">{{ $message }}</p> @enderror
            </div>

            <div class="border-t border-slate-100 pt-4">
                <p class="text-xs text-slate-400 mb-3 font-medium">Leave password fields blank to keep current password.</p>
                <div class="space-y-4">
                    <div>
                        <label class="fms-label">New Password</label>
                        <input type="password" name="password" class="fms-input">
                        @error('password') <p class="fms-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="fms-label">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="fms-input">
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="btn-primary flex-1 justify-center">Save Changes</button>
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