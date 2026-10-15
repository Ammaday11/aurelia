<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreWrappingPaperRequest;
use App\Http\Requests\Admin\UpdateWrappingPaperRequest;
use App\Models\WrappingPaper;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WrappingPaperController extends Controller
{
    public function __construct(protected ImageService $images) {}

    public function index(): View
    {
        $this->authorize('viewAny', WrappingPaper::class);

        return view('admin.wrapping-papers.index', [
            'title' => 'Wrapping Papers',
            'wrappingPapers' => WrappingPaper::query()->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', WrappingPaper::class);

        return view('admin.wrapping-papers.create', ['title' => 'New Wrapping Paper']);
    }

    public function store(StoreWrappingPaperRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image'] = $this->images->storeSingle($request->file('image'), 'wrapping-papers');
        }

        $wrappingPaper = WrappingPaper::create($data);

        return redirect()->route('admin.wrapping-papers.index')->with('success', "\"{$wrappingPaper->name}\" was created.");
    }

    public function edit(WrappingPaper $wrappingPaper): View
    {
        $this->authorize('update', $wrappingPaper);

        return view('admin.wrapping-papers.edit', ['title' => 'Edit Wrapping Paper', 'wrappingPaper' => $wrappingPaper]);
    }

    public function update(UpdateWrappingPaperRequest $request, WrappingPaper $wrappingPaper): RedirectResponse
    {
        $data = $request->safe()->except(['image']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $this->images->delete($wrappingPaper->image);
            $data['image'] = $this->images->storeSingle($request->file('image'), 'wrapping-papers');
        }

        $wrappingPaper->update($data);

        return redirect()->route('admin.wrapping-papers.index')->with('success', "\"{$wrappingPaper->name}\" was updated.");
    }

    public function destroy(WrappingPaper $wrappingPaper): RedirectResponse
    {
        $this->authorize('delete', $wrappingPaper);

        $this->images->delete($wrappingPaper->image);
        $wrappingPaper->delete();

        return redirect()->route('admin.wrapping-papers.index')->with('success', "\"{$wrappingPaper->name}\" was deleted.");
    }
}
