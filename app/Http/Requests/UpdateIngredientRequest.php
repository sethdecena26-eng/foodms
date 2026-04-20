<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateIngredientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        $id = $this->route('inventory')->id;

        return [
            'name'                => "required|string|max:100|unique:ingredients,name,{$id}",
            'unit'                => 'required|string|max:30',
            'low_stock_threshold' => 'required|numeric|min:0',
            'cost_per_unit'       => 'required|numeric|min:0',
            'category'            => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'cost_per_unit.min' => 'Cost per unit cannot be negative.',
        ];
    }
}