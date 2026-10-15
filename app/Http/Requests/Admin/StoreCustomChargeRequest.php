<?php

namespace App\Http\Requests\Admin;

use App\Models\CustomCharge;
use Illuminate\Foundation\Http\FormRequest;

class StoreCustomChargeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', CustomCharge::class);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'amount' => ['required', 'numeric', 'min:0', 'max:9999.99'],
            'is_automatic' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
