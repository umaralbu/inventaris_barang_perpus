<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\CategoryRequest;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of categories.
     */
    public function index(Request $request): View
    {
        $query = Category::withCount(['catalogs', 'items']);

        // Filter Pencarian
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter Status Aktif/Nonaktif
        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $categories = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('master.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        return view('master.categories.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        Category::create($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Kategori barang baru berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): View
    {
        return view('master.categories.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(CategoryRequest $request, Category $category): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        $category->update($validated);

        return redirect()->route('categories.index')
            ->with('success', 'Data kategori berhasil diperbarui.');
    }

    /**
     * Remove or archive the specified category.
     */
    public function destroy(Category $category): RedirectResponse
    {
        $hasUsage = $category->catalogs()->exists() || $category->items()->exists();

        if ($hasUsage) {
            $category->update(['is_active' => false]);

            return redirect()->route('categories.index')
                ->with('warning', 'Kategori ini memiliki ' . ($category->items()->count()) . ' unit barang terkait. Demi integritas audit riwayat, kategori dinonaktifkan (diarsipkan), bukan dihapus permanen.');
        }

        $category->delete();

        return redirect()->route('categories.index')
            ->with('success', 'Kategori berhasil dihapus permanen karena tidak memiliki relasi data.');
    }

    /**
     * Toggle active/inactive status of category.
     */
    public function toggleStatus(Category $category): RedirectResponse
    {
        $category->update(['is_active' => !$category->is_active]);

        $statusText = $category->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return redirect()->back()
            ->with('success', "Status kategori '{$category->name}' berhasil {$statusText}.");
    }
}

