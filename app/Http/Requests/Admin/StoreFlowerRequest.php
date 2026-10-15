<?php

namespace App\Http\Requests\Admin;

use App\Models\Flower;
use Illuminate\Foundation\Http\FormRequest;

class StoreFlowerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Flower::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'price_per_stem' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
