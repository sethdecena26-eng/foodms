<?php

namespace App\Http\Controllers;

use App\Models\Ingredient;
use App\Models\MenuItem;
use App\Models\User;
use Illuminate\Http\Request;

class ArchiveController extends Controller
{
    // ── Archive index pages ───────────────────────────────────────────────────

    public function menuItems()
    {
        $items = MenuItem::onlyTrashed()
                         ->with('ingredients')
                         ->latest('deleted_at')
                         ->paginate(20);

        return view('archives.menu-items', compact('items'));
    }

    public function ingredients()
    {
        $ingredients = Ingredient::onlyTrashed()
                                  ->latest('deleted_at')
                                  ->paginate(20);

        return view('archives.ingredients', compact('ingredients'));
    }

    public function users()
    {
        $users = User::onlyTrashed()
                     ->with('role')
                     ->latest('deleted_at')
                     ->paginate(20);

        return view('archives.users', compact('users'));
    }

    // ── Archive (soft-delete) ─────────────────────────────────────────────────

    public function archiveMenuItem(MenuItem $menuItem)
    {
        $menuItem->delete(); // soft delete
        return back()->with('success', "'{$menuItem->name}' has been archived.");
    }

    public function archiveIngredient(Ingredient $ingredient)
    {
        $ingredient->delete();
        return back()->with('success', "'{$ingredient->name}' has been archived.");
    }

    public function archiveUser(User $user)
    {
        abort_if($user->id === auth()->id(), 403, 'You cannot archive your own account.');
        $user->delete();
        return back()->with('success', "'{$user->name}' has been archived.");
    }

    // ── Restore ───────────────────────────────────────────────────────────────

    public function restoreMenuItem(int $id)
    {
        $item = MenuItem::onlyTrashed()->findOrFail($id);
        $item->restore();
        return back()->with('success', "'{$item->name}' has been restored.");
    }

    public function restoreIngredient(int $id)
    {
        $ingredient = Ingredient::onlyTrashed()->findOrFail($id);
        $ingredient->restore();
        return back()->with('success', "'{$ingredient->name}' has been restored.");
    }

    public function restoreUser(int $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();
        return back()->with('success', "'{$user->name}' has been restored.");
    }

    // ── Permanent delete (double-confirm, admin only) ─────────────────────────

    public function forceDeleteMenuItem(int $id)
    {
        $item = MenuItem::onlyTrashed()->findOrFail($id);
        $item->ingredients()->detach(); // clean pivot
        $item->forceDelete();
        return back()->with('success', "'{$item->name}' permanently deleted.");
    }

    public function forceDeleteIngredient(int $id)
    {
        $ingredient = Ingredient::onlyTrashed()->findOrFail($id);
        $ingredient->forceDelete();
        return back()->with('success', "'{$ingredient->name}' permanently deleted.");
    }

    public function forceDeleteUser(int $id)
    {
        $user = User::onlyTrashed()->findOrFail($id);
        abort_if($user->id === auth()->id(), 403);
        $user->forceDelete();
        return back()->with('success', "'{$user->name}' permanently deleted.");
    }
}