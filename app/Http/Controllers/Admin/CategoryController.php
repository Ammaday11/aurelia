<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;
use App\Models\Category;
use App\Services\ImageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(protected ImageService $images) {}

    public function index(): View
    {
        $this->authorize('viewAny', Category::class);

        return view('admin.categories.index', [
            'title' => 'Categories',
            'categories' => Category::query()->withCount('products')->ordered()->get(),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Category::class);

        return view('admin.categories.create', ['title' => 'New Category']);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->safe()->except(['image']);
        $data['is_active'] = $request->boolean('is_active', true);

        if ($request->hasFile('image')) {
            $data['image'] = $this->images->storeSingle($request->file('image'), 'categories');
        }

        $category = Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', "\"{$category->name}\" was created.");
    }

    public function edit(Category $category): View
    {
        $this->authorize('update', $category);

        return view('admin.categories.edit', ['title' => 'Edit Category', 'category' => $category]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $data = $request->safe()->except(['image']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('image')) {
            $this->images->delete($category->image);
            $data['image'] = $this->images->storeSingle($request->file('image'), 'categories');
        }

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', "\"{$category->name}\" was updated.");
    }

    public function destroy(Category $category): RedirectResponse
    {
        $this->authorize('delete', $category);

        if ($category->products()->exists()) {
            return back()->with('error', "\"{$category->name}\" still has products assigned to it and can't be deleted.");
        }

        $this->images->delete($category->image);
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', "\"{$category->name}\" was deleted.");
    }
}
