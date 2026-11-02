<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreExtraRequest;
use App\Http\Requests\Admin\UpdateExtraRequest;
use App\Models\Extra;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ExtraController extends Controller
{
    public function __construct(protected ImageService $images) {}

    public function index(): View
    {
        $this->authorize('viewAny', Extra::class);

        return view('admin.extras.index', [
            'title' => 'Extras',
            'extras' => Extra::query()->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Extra::class);

        return view('admin.extras.create', ['title' => 'New Extra']);
    }

    public function store(StoreExtraRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image'] = $this->images->storeSingle($request->file('image'), 'extras');
        }

        $extra = Extra::create($data);

        return redirect()->route('admin.extras.index')->with('success', "\"{$extra->name}\" was created.");
    }

    public function edit(Extra $extra): View
    {
        $this->authorize('update', $extra);

        return view('admin.extras.edit', ['title' => 'Edit Extra', 'extra' => $extra]);
    }

    public function update(UpdateExtraRequest $request, Extra $extra): RedirectResponse
    {
        $data = $request->safe()->except(['image']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $this->images->delete($extra->image);
            $data['image'] = $this->images->storeSingle($request->file('image'), 'extras');
        }

        $extra->update($data);

        return redirect()->route('admin.extras.index')->with('success', "\"{$extra->name}\" was updated.");
    }

    public function destroy(Extra $extra): RedirectResponse
    {
        $this->authorize('delete', $extra);

        $this->images->delete($extra->image);
        $extra->delete();

        return redirect()->route('admin.extras.index')->with('success', "\"{$extra->name}\" was deleted.");
    }
}
