<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name'                       => 'required|string|max:100|unique:menu_items,name',
            'description'                => 'nullable|string|max:500',
            'category'                   => 'required|string|max:50',
            'selling_price'              => 'required|numeric|min:0',
            'is_available'               => 'boolean',
            'image'                      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'recipe'                     => 'nullable|array',
            'recipe.*.ingredient_id'     => 'required|distinct|exists:ingredients,id',
            'recipe.*.quantity_required' => 'required|numeric|min:0.001',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique'                       => 'A menu item with this name already exists.',
            'recipe.*.ingredient_id.distinct'   => 'Duplicate ingredients in recipe — combine them into one row.',
            'recipe.*.quantity_required.min'    => 'Quantity must be greater than zero.',
        ];
    }
}