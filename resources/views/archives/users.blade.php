@extends('layouts.app')

@section('title', 'Archives — Users')
@section('page-title', 'Archives')
@section('page-subtitle', 'Archived records — nothing is permanently deleted until you choose')

@section('content')

{{-- Tab navigation --}}
<div class="flex gap-1 mb-6 bg-white border border-slate-100 rounded-xl p-1 w-fit">
    <a href="{{ route('archives.menu-items') }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-colors text-slate-500 hover:text-slate-700 hover:bg-slate-50">
        Menu Items
        @php $mc = \App\Models\MenuItem::onlyTrashed()->count() @endphp
        @if($mc) <span class="ml-1.5 text-xs opacity-70">({{ $mc }})</span> @endif
    </a>
    <a href="{{ route('archives.ingredients') }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-colors text-slate-500 hover:text-slate-700 hover:bg-slate-50">
        Ingredients
        @php $ic = \App\Models\Ingredient::onlyTrashed()->count() @endphp
        @if($ic) <span class="ml-1.5 text-xs opacity-70">({{ $ic }})</span> @endif
    </a>
    <a href="{{ route('archives.users') }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-slate-800 text-white">
        Users
        @php $uc = \App\Models\User::onlyTrashed()->count() @endphp
        @if($uc) <span class="ml-1.5 text-xs opacity-70">({{ $uc }})</span> @endif
    </a>
</div>

{{-- Info banner --}}
<div class="flex items-start gap-3 px-4 py-3 bg-blue-50 border border-blue-100 rounded-xl mb-5 text-sm text-blue-700">
    <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <span>
        Archived records are <strong>hidden from active use</strong> but never lost.
        Restore them at any time. Permanent deletion requires a second confirmation and cannot be undone.
    </span>
</div>

<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h2 class="font-display font-bold text-slate-800">Archived Users</h2>
    </div>

    <table class="fms-table w-full">
        <thead>
            <tr>
                <th class="text-left">User</th>
                <th class="text-left">Email</th>
                <th class="text-center">Role</th>
                <th class="text-left">Archived On</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr class="opacity-70">
                <td>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold text-white bg-slate-400 flex-shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <p class="font-semibold text-slate-600">{{ $user->name }}</p>
                    </div>
                </td>
                <td class="text-sm text-slate-500">{{ $user->email }}</td>
                <td class="text-center">
                    <span class="text-xs font-bold px-2.5 py-1 rounded-full bg-slate-100 text-slate-500 capitalize">
                        {{ $user->role?->name ?? 'No role' }}
                    </span>
                </td>
                <td class="text-sm text-slate-400">{{ $user->deleted_at->format('M d, Y h:i A') }}</td>
                <td class="text-right">
                    <div class="flex items-center justify-end gap-3">
                        <form method="POST" action="{{ route('archives.users.restore', $user->id) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs font-semibold text-emerald-600 hover:underline">
                                Restore
                            </button>
                        </form>
                        <form method="POST" action="{{ route('archives.users.force', $user->id) }}"
                              onsubmit="return confirm('PERMANENTLY delete \'{{ addslashes($user->name) }}\'?\n\nThis CANNOT be undone. All their order history will be orphaned.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-400 hover:text-red-600">
                                Delete Forever
                            </button>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="5" class="text-center py-12">
                    <svg class="w-10 h-10 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                    <p class="text-slate-400 text-sm">No archived users.</p>
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