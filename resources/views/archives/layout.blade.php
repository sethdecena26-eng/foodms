@extends('layouts.app')

@section('title', 'Archives')
@section('page-title', 'Archives')
@section('page-subtitle', 'Archived records — nothing is permanently deleted until you choose')

@section('content')

{{-- Tab navigation --}}
<div class="flex gap-1 mb-6 bg-white border border-slate-100 rounded-xl p-1 w-fit">
    <a href="{{ route('archives.menu-items') }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
              {{ request()->routeIs('archives.menu-items') ? 'bg-slate-800 text-white' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
        Menu Items
        @php $mc = \App\Models\MenuItem::onlyTrashed()->count() @endphp
        @if($mc) <span class="ml-1.5 text-xs opacity-70">({{ $mc }})</span> @endif
    </a>
    <a href="{{ route('archives.ingredients') }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
              {{ request()->routeIs('archives.ingredients') ? 'bg-slate-800 text-white' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
        Ingredients
        @php $ic = \App\Models\Ingredient::onlyTrashed()->count() @endphp
        @if($ic) <span class="ml-1.5 text-xs opacity-70">({{ $ic }})</span> @endif
    </a>
    <a href="{{ route('archives.users') }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
              {{ request()->routeIs('archives.users') ? 'bg-slate-800 text-white' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50' }}">
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

@yield('archive-content')

@endsection