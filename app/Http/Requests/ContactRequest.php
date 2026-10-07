<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContactRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
            // Honeypot field: real visitors never fill in a field named
            // "website" that's hidden with CSS; bots that auto-fill every
            // input trip this and get silently rejected below.
            'website' => ['prohibited'],
        ];
    }

    public function messages(): array
    {
        return [
            'website.prohibited' => 'Something went wrong. Please try again.',
        ];
    }
}
