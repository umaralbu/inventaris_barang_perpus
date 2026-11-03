<?php

namespace App\Http\Controllers;

use App\Enums\ItemCondition;
use App\Enums\ItemUnitStatus;
use App\Enums\TransactionStatus;
use App\Models\Item;
use App\Models\ItemCatalog;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle display of dashboard based on user role.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $totalUnits = Item::where('is_active', true)->count();
            $availableUnits = Item::available()->count();
            $borrowedUnits = Item::where('status', ItemUnitStatus::DIPINJAM->value)->where('is_active', true)->count();
            $brokenUnits = Item::whereIn('condition', [ItemCondition::RUSAK->value, ItemCondition::PERLU_PERBAIKAN->value])
                ->where('is_active', true)
                ->count();
            $pendingApprovalsCount = Transaction::where('status', TransactionStatus::MENUNGGU_PERSETUJUAN->value)->count();
            $overdueCount = Transaction::where('status', TransactionStatus::DIPINJAM->value)
                ->whereNotNull('expected_return_date')
                ->where('expected_return_date', '<', now()->toDateString())
                ->count();

            // Low stock alerts (katalog dengan unit tersedia <= 5)
            $catalogs = ItemCatalog::withCount([
                'units as available_count' => function ($query) {
                    $query->where('is_active', true)
                        ->where('status', ItemUnitStatus::TERSEDIA->value)
                        ->where('condition', ItemCondition::BAIK->value);
                }
            ])->having('available_count', '<=', 5)->take(5)->get();

            $recentTransactions = Transaction::with(['staff', 'user', 'details.item'])
                ->latest()
                ->take(5)
                ->get();

            return view('dashboard', [
                'user'                  => $user,
                'totalUnits'            => $totalUnits,
                'availableUnits'        => $availableUnits,
                'borrowedUnits'         => $borrowedUnits,
                'brokenUnits'           => $brokenUnits,
                'pendingApprovalsCount' => $pendingApprovalsCount,
                'overdueCount'          => $overdueCount,
                'lowStockCatalogs'      => $catalogs,
                'recentTransactions'    => $recentTransactions,
            ]);
        }

        // Tampilan Pegawai
        $myActiveBorrowsCount = Transaction::where('user_id', $user->id)
            ->where('status', TransactionStatus::DIPINJAM->value)
            ->count();
        $myPendingRequestsCount = Transaction::where('user_id', $user->id)
            ->where('status', TransactionStatus::MENUNGGU_PERSETUJUAN->value)
            ->count();
        $myDueThisWeekCount = Transaction::where('user_id', $user->id)
            ->where('status', TransactionStatus::DIPINJAM->value)
            ->whereNotNull('expected_return_date')
            ->whereBetween('expected_return_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
            ->count();
        $myRecentTransactions = Transaction::where('user_id', $user->id)
            ->with(['details.item', 'staff'])
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard', [
            'user'                   => $user,
            'myActiveBorrowsCount'   => $myActiveBorrowsCount,
            'myPendingRequestsCount' => $myPendingRequestsCount,
            'myDueThisWeekCount'     => $myDueThisWeekCount,
            'myRecentTransactions'   => $myRecentTransactions,
        ]);
    }
}

