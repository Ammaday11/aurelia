<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

class AddCustomBouquetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'flowers' => ['required', 'array'],
            'flowers.*' => ['nullable', 'integer', 'min:0', 'max:50'],
            'type' => ['nullable', 'string', 'in:bouquet,boutonniere'],
            'wrapping_paper_id' => ['nullable', 'integer', 'exists:wrapping_papers,id'],
            'ribbon_id' => ['nullable', 'integer', 'exists:ribbons,id'],
            'reference_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:20'],
            'gift_message' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $total = array_sum(array_map('intval', $this->input('flowers', [])));

            if ($total < 1) {
                $validator->errors()->add('flowers', 'Choose at least one flower for your bouquet.');
            }
        });
    }
}
