<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'shop_name' => ['required', 'string', 'max:120'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp,svg', 'max:2048'],
            'favicon' => ['nullable', 'image', 'mimes:png,jpg,jpeg,ico,svg', 'max:1024'],

            'contact_email' => ['nullable', 'email', 'max:190'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'contact_address' => ['nullable', 'string', 'max:500'],
            'opening_hours' => ['nullable', 'string', 'max:190'],
            'social_facebook' => ['nullable', 'url', 'max:255'],
            'social_instagram' => ['nullable', 'url', 'max:255'],
            'social_tiktok' => ['nullable', 'url', 'max:255'],
            // Digits plus an optional leading "+", spaces, and dashes —
            // whatever the admin naturally types — sanitized down to
            // digits-only when building the wa.me link (see setting_whatsapp_link()).
            'whatsapp_number' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9\s\-]+$/'],

            'delivery_time_slots' => ['required', 'array', 'min:1'],
            'delivery_time_slots.*' => ['required', 'string', 'max:60'],

            'bank_name' => ['nullable', 'string', 'max:120'],
            'bank_account_name' => ['nullable', 'string', 'max:120'],
            'bank_account_number' => ['nullable', 'string', 'max:60'],

            'hero_tagline' => ['nullable', 'string', 'max:300'],
            'about_intro' => ['nullable', 'string', 'max:1000'],
            'about_craft_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'about_delivery_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'footer_tagline' => ['nullable', 'string', 'max:300'],
            'meta_description' => ['nullable', 'string', 'max:300'],
        ];
    }
}
