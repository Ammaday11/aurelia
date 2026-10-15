<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCustomChargeRequest;
use App\Http\Requests\Admin\UpdateCustomChargeRequest;
use App\Models\CustomCharge;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomChargeController extends Controller
{
    public function index(): View
    {
        $this->authorize('viewAny', CustomCharge::class);

        return view('admin.custom-charges.index', [
            'title' => 'Custom Charges',
            'customCharges' => CustomCharge::query()->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', CustomCharge::class);

        return view('admin.custom-charges.create', ['title' => 'New Custom Charge']);
    }

    public function store(StoreCustomChargeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active', true);
        $data['is_automatic'] = $request->boolean('is_automatic');

        $customCharge = CustomCharge::create($data);

        return redirect()->route('admin.custom-charges.index')->with('success', "\"{$customCharge->name}\" was created.");
    }

    public function edit(CustomCharge $customCharge): View
    {
        $this->authorize('update', $customCharge);

        return view('admin.custom-charges.edit', ['title' => 'Edit Custom Charge', 'customCharge' => $customCharge]);
    }

    public function update(UpdateCustomChargeRequest $request, CustomCharge $customCharge): RedirectResponse
    {
        $data = $request->validated();
        $data['is_active'] = $request->boolean('is_active');
        $data['is_automatic'] = $request->boolean('is_automatic');

        $customCharge->update($data);

        return redirect()->route('admin.custom-charges.index')->with('success', "\"{$customCharge->name}\" was updated.");
    }

    public function destroy(CustomCharge $customCharge): RedirectResponse
    {
        $this->authorize('delete', $customCharge);

        $customCharge->delete();

        return redirect()->route('admin.custom-charges.index')->with('success', "\"{$customCharge->name}\" was deleted.");
    }
}
