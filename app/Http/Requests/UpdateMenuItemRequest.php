<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $id = $this->route('menuItem')->id;

        return [
            'name'                       => "required|string|max:100|unique:menu_items,name,{$id}",
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
}