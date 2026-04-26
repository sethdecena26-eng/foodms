@extends('layouts.app')

@section('title', 'Archives')
@section('page-title', 'Archives')
@section('page-subtitle', 'Archived records')

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


@yield('archive-content')

@endsection