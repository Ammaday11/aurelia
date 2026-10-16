<?php

namespace App\Http\Requests\Admin;

use App\Models\WrappingPaper;
use Illuminate\Foundation\Http\FormRequest;

class StoreWrappingPaperRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', WrappingPaper::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
