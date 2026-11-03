<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\LocationRequest;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    /**
     * Display a listing of locations/rooms.
     */
    public function index(Request $request): View
    {
        $query = Location::withCount('items');

        // Pencarian
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

        $locations = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('master.locations.index', compact('locations'));
    }

    /**
     * Show the form for creating a new location.
     */
    public function create(): View
    {
        return view('master.locations.create');
    }

    /**
     * Store a newly created location in storage.
     */
    public function store(LocationRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        Location::create($validated);

        return redirect()->route('locations.index')
            ->with('success', 'Lokasi ruangan / rak baru berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified location.
     */
    public function edit(Location $location): View
    {
        return view('master.locations.edit', compact('location'));
    }

    /**
     * Update the specified location in storage.
     */
    public function update(LocationRequest $request, Location $location): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        $location->update($validated);

        return redirect()->route('locations.index')
            ->with('success', 'Data lokasi ruangan berhasil diperbarui.');
    }

    /**
     * Remove or archive the specified location.
     */
    public function destroy(Location $location): RedirectResponse
    {
        $hasUsage = $location->items()->exists()
            || $location->transactionDetailsFrom()->exists()
            || $location->transactionDetailsTo()->exists();

        if ($hasUsage) {
            $location->update(['is_active' => false]);

            return redirect()->route('locations.index')
                ->with('warning', 'Ruangan/lokasi ini memiliki ' . $location->items()->count() . ' aset atau riwayat mutasi terkait. Lokasi dinonaktifkan (diarsipkan), bukan dihapus permanen.');
        }

        $location->delete();

        return redirect()->route('locations.index')
            ->with('success', 'Lokasi ruangan berhasil dihapus permanen karena belum pernah digunakan.');
    }

    /**
     * Toggle active/inactive status of location.
     */
    public function toggleStatus(Location $location): RedirectResponse
    {
        $location->update(['is_active' => !$location->is_active]);

        $statusText = $location->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return redirect()->back()
            ->with('success', "Status lokasi '{$location->name}' berhasil {$statusText}.");
    }
}

