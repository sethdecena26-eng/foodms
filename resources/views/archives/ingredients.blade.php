@extends('layouts.app')

@section('title', 'Archives — Ingredients')
@section('page-title', 'Archives')
@section('page-subtitle', 'Archived records')

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
       class="px-4 py-2 rounded-lg text-sm font-medium transition-colors bg-slate-800 text-white">
        Ingredients
        @php $ic = \App\Models\Ingredient::onlyTrashed()->count() @endphp
        @if($ic) <span class="ml-1.5 text-xs opacity-70">({{ $ic }})</span> @endif
    </a>
    <a href="{{ route('archives.users') }}"
       class="px-4 py-2 rounded-lg text-sm font-medium transition-colors text-slate-500 hover:text-slate-700 hover:bg-slate-50">
        Users
        @php $uc = \App\Models\User::onlyTrashed()->count() @endphp
        @if($uc) <span class="ml-1.5 text-xs opacity-70">({{ $uc }})</span> @endif
    </a>
</div>

{{-- Info banner --}}


<div class="bg-white rounded-2xl border border-slate-100 overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-100">
        <h2 class="font-display font-bold text-slate-800">Archived Ingredients</h2>
    </div>

    <table class="fms-table w-full">
        <thead>
            <tr>
                <th class="text-left">Ingredient</th>
                <th class="text-left">Category</th>
                <th class="text-right">Last Stock</th>
                <th class="text-right">Cost / Unit</th>
                <th class="text-left">Archived On</th>
                <th class="text-right">Actions</th>
            </tr>
        </thead>
        <tbody>
            @forelse($ingredients as $ingredient)
            <tr class="opacity-70">
                <td>
                    <p class="font-semibold text-slate-600">{{ $ingredient->name }}</p>
                    <p class="text-xs text-slate-400">{{ $ingredient->unit }}</p>
                </td>
                <td>
                    <span class="text-xs bg-slate-100 text-slate-500 px-2 py-0.5 rounded-full">
                        {{ $ingredient->category ?? '—' }}
                    </span>
                </td>
                <td class="text-right text-slate-600">{{ number_format($ingredient->quantity_in_stock, 0) }}</td>
                <td class="text-right text-slate-600">₱{{ number_format($ingredient->cost_per_unit, 2) }}</td>
                <td class="text-sm text-slate-400">{{ $ingredient->deleted_at->format('M d, Y h:i A') }}</td>
                <td class="text-right">
                    <div class="flex items-center justify-end gap-3">
                        <form method="POST" action="{{ route('archives.ingredients.restore', $ingredient->id) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="text-xs font-semibold text-emerald-600 hover:underline">
                                Restore
                            </button>
                        </form>
                        <form method="POST" action="{{ route('archives.ingredients.force', $ingredient->id) }}"
                              onsubmit="return confirm('PERMANENTLY delete \'{{ addslashes($ingredient->name) }}\'?\n\nThis CANNOT be undone.')">
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
                <td colspan="6" class="text-center py-12">
                    <svg class="w-10 h-10 text-slate-200 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                    </svg>
                    <p class="text-slate-400 text-sm">No archived ingredients.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="px-5 py-4 border-t border-slate-100">
        {{ $ingredients->links() }}
    </div>
</div>

@endsection