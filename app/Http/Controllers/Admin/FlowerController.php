<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreFlowerRequest;
use App\Http\Requests\Admin\UpdateFlowerRequest;
use App\Models\Flower;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class FlowerController extends Controller
{
    public function __construct(protected ImageService $images) {}

    public function index(): View
    {
        $this->authorize('viewAny', Flower::class);

        return view('admin.flowers.index', [
            'title' => 'Flowers',
            'flowers' => Flower::query()->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Flower::class);

        return view('admin.flowers.create', ['title' => 'New Flower']);
    }

    public function store(StoreFlowerRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image'] = $this->images->storeSingle($request->file('image'), 'flowers');
        }

        $flower = Flower::create($data);

        return redirect()->route('admin.flowers.index')->with('success', "\"{$flower->name}\" was created.");
    }

    public function edit(Flower $flower): View
    {
        $this->authorize('update', $flower);

        return view('admin.flowers.edit', ['title' => 'Edit Flower', 'flower' => $flower]);
    }

    public function update(UpdateFlowerRequest $request, Flower $flower): RedirectResponse
    {
        $data = $request->safe()->except(['image']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $this->images->delete($flower->image);
            $data['image'] = $this->images->storeSingle($request->file('image'), 'flowers');
        }

        $flower->update($data);

        return redirect()->route('admin.flowers.index')->with('success', "\"{$flower->name}\" was updated.");
    }

    public function destroy(Flower $flower): RedirectResponse
    {
        $this->authorize('delete', $flower);

        // Past orders keep their own snapshot (OrderItem::custom_flowers),
        // so deleting a flower never touches order history — unlike
        // Category/Product there's no "still referenced" guard needed.
        $this->images->delete($flower->image);
        $flower->delete();

        return redirect()->route('admin.flowers.index')->with('success', "\"{$flower->name}\" was deleted.");
    }
}
