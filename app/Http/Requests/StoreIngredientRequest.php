<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreIngredientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'name'                => 'required|string|max:100|unique:ingredients,name',
            'unit'                => 'required|string|max:30',
            'quantity_in_stock'   => 'required|numeric|min:0',
            'low_stock_threshold' => 'required|numeric|min:0',
            'cost_per_unit'       => 'required|numeric|min:0',
            'category'            => 'nullable|string|max:50',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'An ingredient with this name already exists.',
        ];
    }
}