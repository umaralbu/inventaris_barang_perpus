<?php

namespace App\Http\Controllers\Master;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Master\UserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of system users.
     */
    public function index(Request $request): View
    {
        $query = User::withCount(['transactions', 'approvedTransactions']);

        // Pencarian Nama, Username, atau Email
        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter Peran (Role)
        if ($role = $request->input('role')) {
            $query->where('role', $role);
        }

        // Filter Status Aktif/Nonaktif
        if ($request->filled('status')) {
            if ($request->input('status') === 'active') {
                $query->where('is_active', true);
            } elseif ($request->input('status') === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('master.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create(): View
    {
        $roles = UserRole::cases();

        return view('master.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(UserRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->boolean('is_active', true);

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'Akun pengguna sistem baru berhasil dibuat.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user): View
    {
        $roles = UserRole::cases();

        return view('master.users.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(UserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['is_active'] = $request->boolean('is_active', true);

        // Pencegahan: Jangan nonaktifkan akun sendiri
        if ($user->id === Auth::id() && !$validated['is_active']) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Anda tidak dapat menonaktifkan akun yang sedang Anda gunakan.');
        }

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'Data akun pengguna berhasil diperbarui.');
    }

    /**
     * Remove or archive the specified user.
     */
    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')
                ->with('error', 'Dilarang menghapus akun yang sedang Anda gunakan untuk login saat ini.');
        }

        $hasUsage = $user->transactions()->exists() || $user->approvedTransactions()->exists();

        if ($hasUsage) {
            $user->update(['is_active' => false]);

            return redirect()->route('users.index')
                ->with('warning', "Pengguna '{$user->name}' memiliki riwayat transaksi/persetujuan di sistem. Akun dinonaktifkan (diarsipkan), bukan dihapus permanen demi jejak audit.");
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'Akun pengguna berhasil dihapus permanen.');
    }

    /**
     * Toggle active/inactive status of user.
     */
    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return redirect()->back()
                ->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $statusText = $user->is_active ? 'diaktifkan kembali' : 'dinonaktifkan';

        return redirect()->back()
            ->with('success', "Akun pengguna '{$user->username}' berhasil {$statusText}.");
    }
}

