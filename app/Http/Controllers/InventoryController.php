<?php

namespace App\Http\Controllers;

use App\Enums\ItemCondition;
use App\Enums\ItemUnitStatus;
use App\Http\Requests\Inventory\StoreCatalogRequest;
use App\Http\Requests\Inventory\StoreUnitRequest;
use App\Http\Requests\Inventory\UpdateCatalogRequest;
use App\Http\Requests\Inventory\UpdateUnitRequest;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemCatalog;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InventoryController extends Controller
{
    /**
     * Display Tingkat 1: Daftar Katalog Barang (Admin & Pegawai).
     */
    public function index(Request $request): View
    {
        $query = ItemCatalog::with('category')->withCount([
            'units as total_units_count',
            'units as available_units_count' => function ($q) {
                $q->where('is_active', true)
                  ->where('status', ItemUnitStatus::TERSEDIA->value)
                  ->where('condition', ItemCondition::BAIK->value);
            }
        ]);

        // 1. Pencarian Teks
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%")
                  ->orWhere('specification', 'like', "%{$search}%")
                  ->orWhereHas('units', function ($uq) use ($search) {
                      $uq->where('code', 'like', "%{$search}%");
                  });
            });
        }

        // 2. Filter Kategori
        if ($categoryId = $request->input('category_id')) {
            $query->where('category_id', $categoryId);
        }

        // 3. Filter Lokasi Unit
        if ($locationId = $request->input('location_id')) {
            $query->whereHas('units', function ($q) use ($locationId) {
                $q->where('location_id', $locationId);
            });
        }

        // 4. Filter Kondisi Fisik Unit
        if ($condition = $request->input('condition')) {
            $query->whereHas('units', function ($q) use ($condition) {
                $q->where('condition', $condition);
            });
        }

        // 5. Filter Status Ketersediaan Unit
        if ($status = $request->input('status')) {
            $query->whereHas('units', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        // 6. Filter Status Katalog (Aktif/Arsip)
        if ($request->filled('is_active')) {
            if ($request->input('is_active') === '1') {
                $query->where('is_active', true);
            } elseif ($request->input('is_active') === '0') {
                $query->where('is_active', false);
            }
        }

        $catalogs = $query->orderBy('name')->paginate(15)->withQueryString();

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();
        $conditions = ItemCondition::cases();
        $unitStatuses = ItemUnitStatus::cases();

        return view('inventory.index', compact(
            'catalogs',
            'categories',
            'locations',
            'conditions',
            'unitStatuses'
        ));
    }

    /**
     * Display Tingkat 2: Detail Katalog & Daftar Seluruh Unit Fisiknya.
     */
    public function show(ItemCatalog $catalog, Request $request): View
    {
        $catalog->load('category');

        $unitsQuery = $catalog->units()->with('location');

        // Filter unit fisik di halaman detail
        if ($codeSearch = $request->input('unit_code')) {
            $unitsQuery->where('code', 'like', "%{$codeSearch}%");
        }
        if ($cond = $request->input('unit_condition')) {
            $unitsQuery->where('condition', $cond);
        }
        if ($stat = $request->input('unit_status')) {
            $unitsQuery->where('status', $stat);
        }
        if ($loc = $request->input('unit_location')) {
            $unitsQuery->where('location_id', $loc);
        }

        $units = $unitsQuery->orderBy('code')->paginate(20)->withQueryString();

        $locations = Location::where('is_active', true)->orderBy('name')->get();
        $conditions = ItemCondition::cases();
        $unitStatuses = ItemUnitStatus::cases();

        return view('inventory.show', compact(
            'catalog',
            'units',
            'locations',
            'conditions',
            'unitStatuses'
        ));
    }

    /**
     * Show form to create new catalog item (Admin only).
     */
    public function create(): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $locations = Location::where('is_active', true)->orderBy('name')->get();

        return view('inventory.create', compact('categories', 'locations'));
    }

    /**
     * Store a newly created catalog item and optionally generate physical units.
     */
    public function store(StoreCatalogRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Upload foto jika ada
        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('catalogs', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        DB::transaction(function () use ($validated, $request, &$catalog) {
            $catalog = ItemCatalog::create([
                'category_id'   => $validated['category_id'],
                'name'          => $validated['name'],
                'brand'         => $validated['brand'] ?? null,
                'specification' => $validated['specification'] ?? null,
                'unit_name'     => $validated['unit_name'],
                'description'   => $validated['description'] ?? null,
                'photo'         => $validated['photo'] ?? null,
                'is_active'     => $validated['is_active'],
            ]);

            // Jika user memilih untuk langsung generate sejumlah unit fisik
            if ($request->boolean('generate_units') && !empty($validated['initial_units_count'])) {
                $count = (int) $validated['initial_units_count'];
                $prefix = !empty($validated['code_prefix'])
                    ? trim($validated['code_prefix'])
                    : ('INV-' . strtoupper(Str::slug(substr($catalog->name, 0, 3))) . '-');
                $locationId = $validated['location_id'] ?? Location::value('id');

                for ($i = 1; $i <= $count; $i++) {
                    $uniqueCode = $prefix . str_pad($i, 3, '0', STR_PAD_LEFT);

                    // Pastikan kode unik, jika sudah ada tambahkan suffix random
                    if (Item::where('code', $uniqueCode)->exists()) {
                        $uniqueCode = $prefix . str_pad($i, 3, '0', STR_PAD_LEFT) . '-' . strtoupper(Str::random(3));
                    }

                    Item::create([
                        'catalog_id'     => $catalog->id,
                        'category_id'    => $catalog->category_id,
                        'location_id'    => $locationId,
                        'code'           => $uniqueCode,
                        'name'           => $catalog->name,
                        'brand'          => $catalog->brand,
                        'specification'  => $catalog->specification,
                        'quantity'       => 1,
                        'unit'           => $catalog->unit_name,
                        'condition'      => ItemCondition::BAIK->value,
                        'status'         => ItemUnitStatus::TERSEDIA->value,
                        'purchase_date'  => $validated['purchase_date'] ?? null,
                        'purchase_price' => $validated['purchase_price'] ?? null,
                        'is_active'      => true,
                    ]);
                }
            }
        });

        return redirect()->route('inventory.show', $catalog)
            ->with('success', "Katalog '{$catalog->name}' berhasil ditambahkan ke inventaris.");
    }

    /**
     * Show form to edit catalog item (Admin only).
     */
    public function edit(ItemCatalog $catalog): View
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('inventory.edit', compact('catalog', 'categories'));
    }

    /**
     * Update catalog item.
     */
    public function update(UpdateCatalogRequest $request, ItemCatalog $catalog): RedirectResponse
    {
        $validated = $request->validated();

        if ($request->hasFile('photo')) {
            if ($catalog->photo && Storage::disk('public')->exists($catalog->photo)) {
                Storage::disk('public')->delete($catalog->photo);
            }
            $validated['photo'] = $request->file('photo')->store('catalogs', 'public');
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        DB::transaction(function () use ($catalog, $validated) {
            $catalog->update($validated);

            // Sinkronkan nama, merek, dan kategori ke unit fisik di bawahnya
            $catalog->units()->update([
                'category_id'   => $catalog->category_id,
                'name'          => $catalog->name,
                'brand'         => $catalog->brand,
                'specification' => $catalog->specification,
                'unit'          => $catalog->unit_name,
            ]);
        });

        return redirect()->route('inventory.show', $catalog)
            ->with('success', 'Data katalog berhasil diperbarui.');
    }

    /**
     * Archive or delete catalog item.
     */
    public function destroy(ItemCatalog $catalog): RedirectResponse
    {
        // Cek apakah ada unit fisik yang pernah tercatat dalam transaksi
        $hasTransactionHistory = $catalog->units()
            ->whereHas('transactionDetails')
            ->exists();

        if ($hasTransactionHistory) {
            $catalog->update(['is_active' => false]);
            $catalog->units()->update(['is_active' => false]);

            return redirect()->route('inventory.index')
                ->with('warning', "Katalog '{$catalog->name}' memiliki unit fisik dengan jejak riwayat transaksi peminjaman/mutasi. Demi kepatuhan audit aset, katalog dan unit fisiknya dinonaktifkan (diarsipkan), bukan dihapus permanen.");
        }

        // Hapus file foto jika ada
        if ($catalog->photo && Storage::disk('public')->exists($catalog->photo)) {
            Storage::disk('public')->delete($catalog->photo);
        }

        // Hapus unit fisik dan katalog
        $catalog->units()->delete();
        $catalog->delete();

        return redirect()->route('inventory.index')
            ->with('success', 'Katalog dan unit fisiknya berhasil dihapus permanen karena belum pernah memiliki transaksi.');
    }

    /**
     * Toggle active/inactive status of catalog.
     */
    public function toggleStatus(ItemCatalog $catalog): RedirectResponse
    {
        $newStatus = !$catalog->is_active;
        $catalog->update(['is_active' => $newStatus]);
        $catalog->units()->update(['is_active' => $newStatus]);

        $statusText = $newStatus ? 'diaktifkan kembali' : 'dinonaktifkan (diarsipkan)';

        return redirect()->back()
            ->with('success', "Katalog '{$catalog->name}' dan seluruh unit fisiknya berhasil {$statusText}.");
    }

    // =========================================================================
    // AKSI UNIT FISIK INDIVIDUAL (TINGKAT 2)
    // =========================================================================

    /**
     * Store a new physical unit under the given catalog.
     */
    public function storeUnit(ItemCatalog $catalog, StoreUnitRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Item::create([
            'catalog_id'     => $catalog->id,
            'category_id'    => $catalog->category_id,
            'location_id'    => $validated['location_id'],
            'code'           => $validated['code'],
            'name'           => $catalog->name,
            'brand'          => $catalog->brand,
            'specification'  => $catalog->specification,
            'quantity'       => 1,
            'unit'           => $catalog->unit_name,
            'condition'      => $validated['condition'],
            'status'         => $validated['status'],
            'purchase_date'  => $validated['purchase_date'] ?? null,
            'purchase_price' => $validated['purchase_price'] ?? null,
            'is_active'      => $request->boolean('is_active', true),
        ]);

        return redirect()->route('inventory.show', $catalog)
            ->with('success', "Unit fisik dengan kode '{$validated['code']}' berhasil ditambahkan.");
    }

    /**
     * Update an individual physical unit.
     */
    public function updateUnit(Item $unit, UpdateUnitRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        $unit->update($validated);

        return redirect()->route('inventory.show', $unit->catalog_id)
            ->with('success', "Data unit fisik '{$unit->code}' berhasil diperbarui.");
    }

    /**
     * Archive or delete an individual physical unit.
     */
    public function destroyUnit(Item $unit): RedirectResponse
    {
        $catalogId = $unit->catalog_id;
        $hasUsage = $unit->transactionDetails()->exists();

        if ($hasUsage) {
            $unit->update(['is_active' => false]);

            return redirect()->route('inventory.show', $catalogId)
                ->with('warning', "Unit fisik '{$unit->code}' memiliki jejak riwayat transaksi sirkulasi. Unit dinonaktifkan (diarsipkan), bukan dihapus permanen.");
        }

        $unit->delete();

        return redirect()->route('inventory.show', $catalogId)
            ->with('success', "Unit fisik '{$unit->code}' berhasil dihapus permanen.");
    }

    /**
     * Toggle active status of an individual physical unit.
     */
    public function toggleUnit(Item $unit): RedirectResponse
    {
        $unit->update(['is_active' => !$unit->is_active]);

        $statusText = $unit->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return redirect()->back()
            ->with('success', "Status unit '{$unit->code}' berhasil {$statusText}.");
    }
}
