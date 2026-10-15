<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateSettingsRequest;
use App\Models\Setting;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(protected ImageService $images) {}

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'title' => 'Settings',
            'timeSlots' => Setting::getJson('delivery_time_slots', config('shop.default_delivery_time_slots')),
        ]);
    }

    public function update(UpdateSettingsRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['logo', 'favicon', 'about_craft_image', 'about_delivery_image', 'delivery_time_slots']);

        // Blank text fields are meaningful here (e.g. clearing the phone
        // number), so every scalar field is written even when empty —
        // unlike product/category forms there's no "leave unchanged".
        Setting::setMany($data);

        Setting::set('delivery_time_slots', json_encode(array_values(array_filter($request->input('delivery_time_slots', [])))));

        if ($request->hasFile('logo')) {
            Setting::set('logo_path', $this->images->storeSingle($request->file('logo'), 'branding', 600, trimTransparentPadding: true));
        }

        if ($request->hasFile('favicon')) {
            Setting::set('favicon_path', $this->images->storeSingle($request->file('favicon'), 'branding', 256, trimTransparentPadding: true));
        }

        if ($request->hasFile('about_craft_image')) {
            Setting::set('about_craft_image_path', $this->images->storeSingle($request->file('about_craft_image'), 'about', 1200));
        }

        if ($request->hasFile('about_delivery_image')) {
            Setting::set('about_delivery_image_path', $this->images->storeSingle($request->file('about_delivery_image'), 'about', 1200));
        }

        return redirect()->route('admin.settings.edit')->with('success', 'Settings updated.');
    }
}
