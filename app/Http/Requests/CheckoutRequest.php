<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s()]{6,30}$/'],
            'email' => ['required', 'email', 'max:190'],
            'delivery_address' => ['required', 'string', 'max:500'],
            'delivery_date' => ['required', 'date', 'after:today'],
            'delivery_time_slot' => ['required', 'string', Rule::in($this->availableTimeSlots())],
            'gift_message' => ['nullable', 'string', 'max:500'],
            'special_notes' => ['nullable', 'string', 'max:1000'],
            'payment_method' => ['required', Rule::in(array_keys(config('shop.payment_methods')))],
            'payment_receipt' => [
                Rule::requiredIf($this->input('payment_method') === 'bank_transfer'),
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:4096',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'delivery_date.after' => 'Please choose a delivery date starting tomorrow, so our florists have time to prepare your order.',
            'payment_receipt.required' => 'Please upload a photo or screenshot of your bank transfer receipt.',
            'phone.regex' => 'Please enter a valid phone number.',
        ];
    }

    protected function availableTimeSlots(): array
    {
        return Setting::getJson('delivery_time_slots', config('shop.default_delivery_time_slots'));
    }
}
