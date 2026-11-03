<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StaffRequest;
use App\Models\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StaffController extends Controller
{
    /**
     * Display a listing of library staff.
     */
    public function index(Request $request): View
    {
        $query = Staff::withCount('transactions');

        // Pencarian Nama Staf
        if ($search = $request->input('search')) {
            $query->where('name', 'like', "%{$search}%");
        }

        // Filter Status Aktif/Nonaktif
        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $staff = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('master.staff.index', compact('staff'));
    }

    /**
     * Show the form for creating a new staff member.
     */
    public function create(): View
    {
        return view('master.staff.create');
    }

    /**
     * Store a newly created staff member in storage.
     */
    public function store(StaffRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        Staff::create($validated);

        return redirect()->route('staff.index')
            ->with('success', 'Staf perpustakaan baru berhasil didaftarkan.');
    }

    /**
     * Show the form for editing the specified staff member.
     */
    public function edit(Staff $staff): View
    {
        return view('master.staff.edit', compact('staff'));
    }

    /**
     * Update the specified staff member in storage.
     */
    public function update(StaffRequest $request, Staff $staff): RedirectResponse
    {
        $validated = $request->validated();
        $validated['is_active'] = $request->boolean('is_active', true);

        $staff->update($validated);

        return redirect()->route('staff.index')
            ->with('success', 'Data staf perpustakaan berhasil diperbarui.');
    }

    /**
     * Remove or archive the specified staff member.
     */
    public function destroy(Staff $staff): RedirectResponse
    {
        $hasTransactions = $staff->transactions()->exists();

        if ($hasTransactions) {
            $staff->update(['is_active' => false]);

            return redirect()->route('staff.index')
                ->with('warning', "Staf '{$staff->name}' memiliki catatan transaksi sirkulasi barang. Demi kepatuhan audit aset, akun dinonaktifkan (diarsipkan), bukan dihapus permanen.");
        }

        $staff->delete();

        return redirect()->route('staff.index')
            ->with('success', 'Data staf berhasil dihapus permanen.');
    }

    /**
     * Toggle active/inactive status of staff.
     */
    public function toggleStatus(Staff $staff): RedirectResponse
    {
        $staff->update(['is_active' => !$staff->is_active]);

        $statusText = $staff->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return redirect()->back()
            ->with('success', "Status staf '{$staff->name}' berhasil {$statusText}.");
    }
}

