<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminCategoryRequest;
use App\Models\Category;
use App\Providers\AppServiceProvider;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * List categories with product counts.
     */
    public function index(Request $request): View
    {
        return view('admin.categories.index', [
            'categories' => Category::query()
                ->withCount('products')
                ->withCount('children')
                ->latest()
                ->paginate(10)
                ->withQueryString(),
        ]);
    }

    /**
     * Show the create category form.
     */
    public function create(): View
    {
        return view('admin.categories.create', [
            'parents' => $this->parents(),
        ]);
    }

    /**
     * Persist a new category.
     */
    public function store(AdminCategoryRequest $request): RedirectResponse
    {
        $category = Category::create($request->validated());

        $this->forgetNavigationCache();

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category created.');
    }

    /**
     * Show the edit category form.
     */
    public function edit(Category $category): View
    {
        return view('admin.categories.edit', [
            'category' => $category,
            'parents' => $this->parents($category),
        ]);
    }

    /**
     * Update an existing category.
     */
    public function update(AdminCategoryRequest $request, Category $category): RedirectResponse
    {
        $category->update($request->validated());

        $this->forgetNavigationCache();

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category updated.');
    }

    /**
     * Delete a category that has no products.
     *
     * The products table refuses to lose its category (database foreign
     * key), so the guard runs first and returns a friendly message
     * instead of a database error. Child categories are detached by the
     * foreign key when the parent is removed.
     */
    public function destroy(Category $category): RedirectResponse
    {
        if ($category->products()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'This category still has products. Move or delete them first.',
            ]);
        }

        $category->delete();

        $this->forgetNavigationCache();

        return redirect()
            ->route('admin.categories.index')
            ->with('status', 'Category deleted.');
    }

    /**
     * Drop the cached storefront navigation so the change is visible at once.
     */
    private function forgetNavigationCache(): void
    {
        Cache::forget(AppServiceProvider::NAVIGATION_CATEGORIES_CACHE_KEY);
    }

    /**
     * Candidate parent categories, excluding the category being edited so
     * a category can never become its own parent.
     *
     * @return Collection<int, Category>
     */
    private function parents(?Category $excluding = null): Collection
    {
        return Category::query()
            ->when($excluding !== null, fn ($query) => $query->whereKeyNot($excluding->getKey()))
            ->orderBy('name')
            ->get();
    }
}
