<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRibbonRequest;
use App\Http\Requests\Admin\UpdateRibbonRequest;
use App\Models\Ribbon;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class RibbonController extends Controller
{
    public function __construct(protected ImageService $images) {}

    public function index(): View
    {
        $this->authorize('viewAny', Ribbon::class);

        return view('admin.ribbons.index', [
            'title' => 'Ribbons',
            'ribbons' => Ribbon::query()->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Ribbon::class);

        return view('admin.ribbons.create', ['title' => 'New Ribbon']);
    }

    public function store(StoreRibbonRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image'] = $this->images->storeSingle($request->file('image'), 'ribbons');
        }

        $ribbon = Ribbon::create($data);

        return redirect()->route('admin.ribbons.index')->with('success', "\"{$ribbon->name}\" was created.");
    }

    public function edit(Ribbon $ribbon): View
    {
        $this->authorize('update', $ribbon);

        return view('admin.ribbons.edit', ['title' => 'Edit Ribbon', 'ribbon' => $ribbon]);
    }

    public function update(UpdateRibbonRequest $request, Ribbon $ribbon): RedirectResponse
    {
        $data = $request->safe()->except(['image']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $this->images->delete($ribbon->image);
            $data['image'] = $this->images->storeSingle($request->file('image'), 'ribbons');
        }

        $ribbon->update($data);

        return redirect()->route('admin.ribbons.index')->with('success', "\"{$ribbon->name}\" was updated.");
    }

    public function destroy(Ribbon $ribbon): RedirectResponse
    {
        $this->authorize('delete', $ribbon);

        $this->images->delete($ribbon->image);
        $ribbon->delete();

        return redirect()->route('admin.ribbons.index')->with('success', "\"{$ribbon->name}\" was deleted.");
    }
}
