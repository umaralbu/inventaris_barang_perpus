<?php

namespace App\Http\Controllers;

use App\Enums\ItemCondition;
use App\Enums\ItemUnitStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Requests\Transaction\ApproveBorrowRequest;
use App\Http\Requests\Transaction\BorrowRequest;
use App\Http\Requests\Transaction\CompleteMaintenanceRequest;
use App\Http\Requests\Transaction\RejectBorrowRequest;
use App\Http\Requests\Transaction\ReturnRequest;
use App\Http\Requests\Transaction\StartMaintenanceRequest;
use App\Http\Requests\Transaction\TransferRequest;
use App\Models\Item;
use App\Models\ItemCatalog;
use App\Models\Location;
use App\Models\Staff;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    /**
     * Display a listing of all transactions (Borrow, Return, Transfer, Maintenance).
     * Pegawai only sees their own transactions; Admin sees all.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $query = Transaction::with(['catalog.category', 'staff', 'user', 'approver', 'destinationLocation', 'sourceLocation', 'details.item'])
            ->latest('transaction_date')
            ->latest('id');

        // Non-admin users only see transactions they initiated
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by transaction type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        // Search by code, staff name, or catalog name
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhereHas('staff', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%");
                  })
                  ->orWhereHas('catalog', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->paginate(15)->appends($request->query());

        $statuses = TransactionStatus::cases();
        $types = TransactionType::cases();

        return view('transactions.index', compact('transactions', 'statuses', 'types'));
    }

    /**
     * Display pending borrow approvals (Admin only).
     */
    public function pending(Request $request): View
    {
        $query = Transaction::with(['catalog.category', 'staff', 'user', 'destinationLocation'])
            ->where('status', TransactionStatus::MENUNGGU_PERSETUJUAN->value)
            ->latest('created_at');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhereHas('staff', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('catalog', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $pendingTransactions = $query->paginate(15)->appends($request->query());

        return view('transactions.pending', compact('pendingTransactions'));
    }

    /**
     * Show the borrow request form.
     */
    public function createBorrow(Request $request): View
    {
        $catalogs = ItemCatalog::with('category')
            ->active()
            ->withCount(['units as available_units_count' => function ($q) {
                $q->where('is_active', true)
                  ->where('status', ItemUnitStatus::TERSEDIA->value)
                  ->where('condition', ItemCondition::BAIK->value);
            }])
            ->orderBy('name')
            ->get();

        $staffMembers = Staff::active()->orderBy('name')->get();
        $locations = Location::active()->orderBy('name')->get();

        $selectedCatalogId = $request->query('catalog_id');

        return view('transactions.borrow.create', compact('catalogs', 'staffMembers', 'locations', 'selectedCatalogId'));
    }

    /**
     * Store a newly created borrow request in storage.
     */
    public function storeBorrow(BorrowRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $location = Location::findOrFail($validated['to_location_id']);

        $code = $this->generateTransactionCode('TRX');

        $transaction = Transaction::create([
            'transaction_code'     => $code,
            'catalog_id'           => $validated['catalog_id'],
            'user_id'              => Auth::id(),
            'staff_id'             => $validated['staff_id'],
            'to_location_id'       => $location->id,
            'to_location'          => $location->name,
            'type'                 => TransactionType::BORROW,
            'status'               => TransactionStatus::MENUNGGU_PERSETUJUAN,
            'quantity'             => $validated['quantity'],
            'transaction_date'     => $validated['transaction_date'],
            'expected_return_date' => $validated['expected_return_date'],
            'notes'                => $validated['notes'] ?? null,
        ]);

        return redirect()->route('transactions.show', $transaction)
            ->with('success', "Permohonan pinjam berhasil diajukan dengan kode: {$transaction->transaction_code}. Menunggu persetujuan admin.");
    }

    /**
     * Show detail of a single transaction.
     */
    public function show(Transaction $transaction): View
    {
        if (!Auth::user()->isAdmin() && $transaction->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat permohonan ini.');
        }

        $transaction->load([
            'catalog.category',
            'staff',
            'user',
            'approver',
            'destinationLocation',
            'sourceLocation',
            'details.item.location',
            'details.fromLocation',
            'details.toLocation',
            'returnTransactions',
        ]);

        return view('transactions.show', compact('transaction'));
    }

    /**
     * Show approval page for a pending borrow request (Admin only).
     */
    public function showApproval(Transaction $transaction): View|RedirectResponse
    {
        if ($transaction->status !== TransactionStatus::MENUNGGU_PERSETUJUAN) {
            return redirect()->route('transactions.show', $transaction)
                ->with('warning', 'Permohonan transaksi ini sudah diproses dan tidak lagi berstatus Menunggu Persetujuan.');
        }

        $transaction->load(['catalog.category', 'staff', 'user', 'destinationLocation']);

        $availableUnits = Item::with('location')
            ->where('catalog_id', $transaction->catalog_id)
            ->where('status', ItemUnitStatus::TERSEDIA->value)
            ->where('condition', ItemCondition::BAIK->value)
            ->where('is_active', true)
            ->orderBy('code')
            ->get();

        return view('transactions.approve', compact('transaction', 'availableUnits'));
    }

    /**
     * Approve a borrow request and allocate physical item units atomically.
     */
    public function approve(ApproveBorrowRequest $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validated();
        $user = Auth::user();

        try {
            DB::transaction(function () use ($transaction, $validated, $user) {
                /** @var Transaction $lockedTransaction */
                $lockedTransaction = Transaction::where('id', $transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedTransaction->status !== TransactionStatus::MENUNGGU_PERSETUJUAN) {
                    throw new \Exception('Permohonan ini telah diproses sebelumnya oleh petugas lain.');
                }

                $units = Item::whereIn('id', $validated['unit_ids'])
                    ->lockForUpdate()
                    ->get();

                foreach ($units as $unit) {
                    if ($unit->status !== ItemUnitStatus::TERSEDIA || $unit->condition !== ItemCondition::BAIK || !$unit->is_active) {
                        throw new \Exception("Unit fisik dengan kode [{$unit->code}] sudah tidak tersedia atau tidak dalam kondisi baik.");
                    }
                }

                if ($units->count() !== (int) $lockedTransaction->quantity) {
                    throw new \Exception('Jumlah unit yang dialokasikan tidak sesuai dengan permohonan.');
                }

                foreach ($units as $unit) {
                    TransactionDetail::create([
                        'transaction_id'   => $lockedTransaction->id,
                        'item_id'          => $unit->id,
                        'from_location_id' => $unit->location_id,
                        'to_location_id'   => $lockedTransaction->to_location_id,
                        'condition_before' => $unit->condition,
                        'condition_after'  => null,
                        'is_returned'      => false,
                        'returned_at'      => null,
                    ]);

                    $unit->update([
                        'status'      => ItemUnitStatus::DIPINJAM,
                        'location_id' => $lockedTransaction->to_location_id ?? $unit->location_id,
                    ]);
                }

                $lockedTransaction->update([
                    'status'              => TransactionStatus::DIPINJAM,
                    'approved_by_user_id' => $user->id,
                    'notes'               => $validated['notes'] ?? $lockedTransaction->notes,
                ]);
            });

            return redirect()->route('transactions.show', $transaction)
                ->with('success', "Permohonan pinjam {$transaction->transaction_code} berhasil disetujui. Unit fisik telah dialokasikan.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memproses persetujuan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Reject a borrow request with a required rejection reason.
     */
    public function reject(RejectBorrowRequest $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validated();
        $user = Auth::user();

        try {
            DB::transaction(function () use ($transaction, $validated, $user) {
                /** @var Transaction $lockedTransaction */
                $lockedTransaction = Transaction::where('id', $transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedTransaction->status !== TransactionStatus::MENUNGGU_PERSETUJUAN) {
                    throw new \Exception('Permohonan ini telah diproses sebelumnya.');
                }

                $lockedTransaction->update([
                    'status'              => TransactionStatus::DITOLAK,
                    'approved_by_user_id' => $user->id,
                    'rejection_reason'    => $validated['rejection_reason'],
                ]);
            });

            return redirect()->route('transactions.show', $transaction)
                ->with('success', "Permohonan pinjam {$transaction->transaction_code} telah ditolak.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menolak permohonan: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a pending borrow request by the requester or admin.
     */
    public function cancel(Request $request, Transaction $transaction): RedirectResponse
    {
        $user = Auth::user();

        if (!$transaction->isCancellableBy($user)) {
            return redirect()->back()->with('error', 'Anda tidak memiliki hak untuk membatalkan transaksi ini atau status transaksi sudah bukan Menunggu Persetujuan.');
        }

        try {
            DB::transaction(function () use ($transaction) {
                /** @var Transaction $lockedTransaction */
                $lockedTransaction = Transaction::where('id', $transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedTransaction->status !== TransactionStatus::MENUNGGU_PERSETUJUAN) {
                    throw new \Exception('Transaksi ini sudah diproses dan tidak dapat dibatalkan.');
                }

                $lockedTransaction->update([
                    'status' => TransactionStatus::DIBATALKAN,
                ]);
            });

            return redirect()->route('transactions.show', $transaction)
                ->with('success', "Permohonan pinjam {$transaction->transaction_code} berhasil dibatalkan.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }

    // =========================================================================
    // MODUL PENGEMBALIAN BARANG (RETURN & PARTIAL RETURN)
    // =========================================================================

    /**
     * Show the return form. Supports full and partial returns.
     */
    public function createReturn(Request $request): View
    {
        $user = Auth::user();

        // Query active loans (Dipinjam or Sebagian Dikembalikan)
        $borrowQuery = Transaction::with(['catalog.category', 'staff', 'destinationLocation', 'details' => function ($q) {
            $q->where('is_returned', false)->with('item.location');
        }])
        ->whereIn('status', [TransactionStatus::DIPINJAM->value, TransactionStatus::SEBAGIAN_DIKEMBALIKAN->value])
        ->where('type', TransactionType::BORROW->value)
        ->latest('transaction_date');

        if (!$user->isAdmin()) {
            $borrowQuery->where('user_id', $user->id);
        }

        $activeBorrows = $borrowQuery->get();

        $selectedBorrow = null;
        if ($request->filled('borrow_id')) {
            $selectedBorrow = $activeBorrows->firstWhere('id', (int) $request->borrow_id);
            if (!$selectedBorrow && $user->isAdmin()) {
                $selectedBorrow = Transaction::with(['catalog.category', 'staff', 'destinationLocation', 'details' => function ($q) {
                    $q->where('is_returned', false)->with('item.location');
                }])
                ->where('id', (int) $request->borrow_id)
                ->whereIn('status', [TransactionStatus::DIPINJAM->value, TransactionStatus::SEBAGIAN_DIKEMBALIKAN->value])
                ->first();
            }
        }

        $locations = Location::active()->orderBy('name')->get();
        $staffMembers = Staff::active()->orderBy('name')->get();
        $conditions = ItemCondition::cases();

        return view('transactions.return.create', compact('activeBorrows', 'selectedBorrow', 'locations', 'staffMembers', 'conditions'));
    }

    /**
     * Store return transaction (Full or Partial return).
     */
    public function storeReturn(ReturnRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = Auth::user();

        try {
            $returnTransaction = DB::transaction(function () use ($validated, $user) {
                /** @var Transaction $borrowTx */
                $borrowTx = Transaction::where('id', $validated['borrow_transaction_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!in_array($borrowTx->status, [TransactionStatus::DIPINJAM, TransactionStatus::SEBAGIAN_DIKEMBALIKAN])) {
                    throw new \Exception("Transaksi pinjaman {$borrowTx->transaction_code} sudah tidak dalam status aktif.");
                }

                $selectedUnitData = collect($validated['units'])->filter(fn ($u) => !empty($u['selected']));
                if ($selectedUnitData->isEmpty()) {
                    throw new \Exception('Minimal satu unit fisik harus dicentang untuk dikembalikan.');
                }

                $selectedItemIds = $selectedUnitData->pluck('item_id')->toArray();
                $units = Item::whereIn('id', $selectedItemIds)->lockForUpdate()->get();

                $targetLocation = Location::findOrFail($validated['return_location_id']);
                $returnCode = $this->generateTransactionCode('RET');

                // Create Return Transaction Header
                $returnTx = Transaction::create([
                    'transaction_code'      => $returnCode,
                    'borrow_transaction_id' => $borrowTx->id,
                    'catalog_id'            => $borrowTx->catalog_id,
                    'user_id'               => $user->id,
                    'approved_by_user_id'   => $user->isAdmin() ? $user->id : null,
                    'staff_id'              => $validated['staff_id'],
                    'to_location_id'        => $targetLocation->id,
                    'to_location'           => $targetLocation->name,
                    'type'                  => TransactionType::RETURN,
                    'status'                => TransactionStatus::DIKEMBALIKAN,
                    'quantity'              => $selectedUnitData->count(),
                    'transaction_date'      => $validated['return_date'],
                    'notes'                 => $validated['notes'] ?? null,
                ]);

                // Process each unit returned
                foreach ($selectedUnitData as $uData) {
                    $unit = $units->firstWhere('id', (int) $uData['item_id']);
                    if (!$unit) {
                        continue;
                    }

                    $borrowDetail = TransactionDetail::where('transaction_id', $borrowTx->id)
                        ->where('item_id', $unit->id)
                        ->where('is_returned', false)
                        ->first();

                    $newCondition = ItemCondition::from($uData['condition']);

                    // Update detail in the original borrow transaction
                    if ($borrowDetail) {
                        $borrowDetail->update([
                            'condition_after' => $newCondition,
                            'is_returned'     => true,
                            'returned_at'     => $validated['return_date'],
                        ]);
                    }

                    // Create detail record for this return transaction
                    TransactionDetail::create([
                        'transaction_id'   => $returnTx->id,
                        'item_id'          => $unit->id,
                        'from_location_id' => $unit->location_id,
                        'to_location_id'   => $targetLocation->id,
                        'condition_before' => $borrowDetail?->condition_before ?? $unit->condition,
                        'condition_after'  => $newCondition,
                        'is_returned'      => true,
                        'returned_at'      => $validated['return_date'],
                    ]);

                    // Update physical unit status and location
                    // If Baik -> Tersedia; if Rusak / Perlu Perbaikan -> Dalam Perbaikan
                    $newUnitStatus = ($newCondition === ItemCondition::BAIK)
                        ? ItemUnitStatus::TERSEDIA
                        : ItemUnitStatus::DALAM_PERBAIKAN;

                    $unit->update([
                        'condition'   => $newCondition,
                        'status'      => $newUnitStatus,
                        'location_id' => $targetLocation->id,
                    ]);
                }

                // Check remaining unreturned units on original borrow transaction
                $unreturnedCount = TransactionDetail::where('transaction_id', $borrowTx->id)
                    ->where('is_returned', false)
                    ->count();

                if ($unreturnedCount === 0) {
                    $borrowTx->update(['status' => TransactionStatus::DIKEMBALIKAN]);
                } else {
                    $borrowTx->update(['status' => TransactionStatus::SEBAGIAN_DIKEMBALIKAN]);
                }

                return $returnTx;
            });

            return redirect()->route('transactions.show', $returnTransaction)
                ->with('success', "Pengembalian barang berhasil dicatat dengan kode: {$returnTransaction->transaction_code}.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memproses pengembalian: ' . $e->getMessage())
                ->withInput();
        }
    }

    // =========================================================================
    // MODUL PERPINDAHAN LOKASI MASSAL (BATCH TRANSFER - KHUSUS ADMIN)
    // =========================================================================

    /**
     * Show the batch transfer form (Admin only).
     */
    public function createTransfer(Request $request): View
    {
        $locations = Location::active()->orderBy('name')->get();
        $staffMembers = Staff::active()->orderBy('name')->get();

        $selectedFromLocationId = $request->query('from_location_id');
        $availableUnits = collect();

        if ($selectedFromLocationId) {
            $availableUnits = Item::with(['catalog.category', 'location'])
                ->where('location_id', $selectedFromLocationId)
                ->where('status', ItemUnitStatus::TERSEDIA->value)
                ->where('is_active', true)
                ->orderBy('code')
                ->get();
        }

        return view('transactions.transfer.create', compact('locations', 'staffMembers', 'selectedFromLocationId', 'availableUnits'));
    }

    /**
     * Store batch transfer transaction (Admin only).
     */
    public function storeTransfer(TransferRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = Auth::user();

        try {
            $transferTx = DB::transaction(function () use ($validated, $user) {
                $fromLocation = Location::findOrFail($validated['from_location_id']);
                $toLocation = Location::findOrFail($validated['to_location_id']);

                $units = Item::whereIn('id', $validated['unit_ids'])
                    ->lockForUpdate()
                    ->get();

                foreach ($units as $unit) {
                    if ($unit->location_id != $fromLocation->id || $unit->status !== ItemUnitStatus::TERSEDIA) {
                        throw new \Exception("Unit [{$unit->code}] sudah tidak berada di {$fromLocation->name} atau statusnya bukan Tersedia.");
                    }
                }

                $transferCode = $this->generateTransactionCode('TRF');

                $transfer = Transaction::create([
                    'transaction_code'    => $transferCode,
                    'user_id'             => $user->id,
                    'approved_by_user_id' => $user->id,
                    'staff_id'            => $validated['staff_id'],
                    'from_location_id'    => $fromLocation->id,
                    'from_location'       => $fromLocation->name,
                    'to_location_id'      => $toLocation->id,
                    'to_location'         => $toLocation->name,
                    'type'                => TransactionType::TRANSFER,
                    'status'              => TransactionStatus::SELESAI,
                    'quantity'            => $units->count(),
                    'transaction_date'    => $validated['transaction_date'],
                    'notes'               => $validated['notes'] ?? null,
                ]);

                foreach ($units as $unit) {
                    TransactionDetail::create([
                        'transaction_id'   => $transfer->id,
                        'item_id'          => $unit->id,
                        'from_location_id' => $fromLocation->id,
                        'to_location_id'   => $toLocation->id,
                        'condition_before' => $unit->condition,
                        'condition_after'  => $unit->condition,
                        'is_returned'      => true,
                        'returned_at'      => $validated['transaction_date'],
                    ]);

                    $unit->update([
                        'location_id' => $toLocation->id,
                    ]);
                }

                return $transfer;
            });

            return redirect()->route('transactions.show', $transferTx)
                ->with('success', "Mutasi massal sebanyak {$transferTx->quantity} unit berhasil dilakukan dari {$transferTx->from_location} ke {$transferTx->to_location}.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memproses mutasi lokasi: ' . $e->getMessage())
                ->withInput();
        }
    }

    // =========================================================================
    // MODUL PEMELIHARAAN BARANG RUSAK (MAINTENANCE LIFECYCLE - KHUSUS ADMIN)
    // =========================================================================

    /**
     * Display maintenance list (Damaged units, In progress repairs, Maintenance history).
     */
    public function maintenanceIndex(Request $request): View
    {
        $tab = $request->query('tab', 'needed');

        // Units that need repair (Condition: Rusak/Perlu Perbaikan and NOT already in repair)
        $neededUnits = Item::with(['catalog.category', 'location'])
            ->whereIn('condition', [ItemCondition::RUSAK->value, ItemCondition::PERLU_PERBAIKAN->value])
            ->where('status', '!=', ItemUnitStatus::DALAM_PERBAIKAN->value)
            ->where('status', '!=', ItemUnitStatus::NONAKTIF->value)
            ->where('is_active', true)
            ->latest('updated_at')
            ->paginate(15, ['*'], 'needed_page');

        // Units currently in repair
        $inProgressUnits = Item::with(['catalog.category', 'location'])
            ->where('status', ItemUnitStatus::DALAM_PERBAIKAN->value)
            ->where('is_active', true)
            ->latest('updated_at')
            ->paginate(15, ['*'], 'progress_page');

        // Active maintenance transactions for quick completion modal
        $activeMaintenanceTransactions = Transaction::with(['item', 'catalog'])
            ->where('type', TransactionType::MAINTENANCE->value)
            ->where('status', TransactionStatus::DIPINJAM->value)
            ->get()
            ->keyBy('item_id');

        // Maintenance history transactions
        $maintenanceHistory = Transaction::with(['item.catalog', 'catalog', 'user'])
            ->where('type', TransactionType::MAINTENANCE->value)
            ->latest('transaction_date')
            ->paginate(15, ['*'], 'history_page');

        $locations = Location::active()->orderBy('name')->get();

        return view('transactions.maintenance.index', compact(
            'tab',
            'neededUnits',
            'inProgressUnits',
            'activeMaintenanceTransactions',
            'maintenanceHistory',
            'locations'
        ));
    }

    /**
     * Start maintenance / send physical item for repair (Admin only).
     */
    public function startMaintenance(StartMaintenanceRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = Auth::user();

        try {
            $maintenanceTx = DB::transaction(function () use ($validated, $user) {
                /** @var Item $unit */
                $unit = Item::where('id', $validated['item_id'])
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($unit->status === ItemUnitStatus::DIPINJAM) {
                    throw new \Exception("Unit [{$unit->code}] sedang dipinjam dan tidak dapat dikirim untuk servis.");
                }

                $code = $this->generateTransactionCode('MNT');

                $tx = Transaction::create([
                    'transaction_code'    => $code,
                    'item_id'             => $unit->id,
                    'catalog_id'          => $unit->catalog_id,
                    'user_id'             => $user->id,
                    'approved_by_user_id' => $user->id,
                    'to_person'           => $validated['vendor_technician'],
                    'type'                => TransactionType::MAINTENANCE,
                    'status'              => TransactionStatus::DIPINJAM,
                    'quantity'            => 1,
                    'transaction_date'    => $validated['start_date'],
                    'notes'               => $validated['issue_description'] . ($validated['estimated_cost'] ? " [Est. Biaya: Rp " . number_format($validated['estimated_cost'], 0, ',', '.') . "]" : ""),
                ]);

                TransactionDetail::create([
                    'transaction_id'   => $tx->id,
                    'item_id'          => $unit->id,
                    'from_location_id' => $unit->location_id,
                    'condition_before' => $unit->condition,
                    'is_returned'      => false,
                ]);

                $unit->update([
                    'status' => ItemUnitStatus::DALAM_PERBAIKAN,
                ]);

                return $tx;
            });

            return redirect()->route('transactions.maintenance.index', ['tab' => 'in_progress'])
                ->with('success', "Unit fisik berhasil didaftarkan ke status 'Dalam Perbaikan' (Kode: {$maintenanceTx->transaction_code}).");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal memulai servis barang: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Complete maintenance / mark repaired or unrepairable (Admin only).
     */
    public function completeMaintenance(CompleteMaintenanceRequest $request, Transaction $transaction): RedirectResponse
    {
        $validated = $request->validated();

        try {
            DB::transaction(function () use ($transaction, $validated) {
                /** @var Transaction $lockedTx */
                $lockedTx = Transaction::where('id', $transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                /** @var Item $unit */
                $unit = Item::where('id', $lockedTx->item_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $detail = TransactionDetail::where('transaction_id', $lockedTx->id)
                    ->where('item_id', $unit->id)
                    ->first();

                if ($validated['result_status'] === 'success') {
                    // Repaired successfully: Baik & Tersedia
                    $unit->update([
                        'condition'   => ItemCondition::BAIK,
                        'status'      => ItemUnitStatus::TERSEDIA,
                        'location_id' => $validated['to_location_id'],
                    ]);

                    if ($detail) {
                        $detail->update([
                            'to_location_id'  => $validated['to_location_id'],
                            'condition_after' => ItemCondition::BAIK,
                            'is_returned'     => true,
                            'returned_at'     => $validated['completion_date'],
                        ]);
                    }

                    $lockedTx->update([
                        'status' => TransactionStatus::SELESAI,
                        'notes'  => $lockedTx->notes . " | Selesai Servis: " . $validated['resolution_notes'] . ($validated['actual_cost'] ? " [Biaya Riil: Rp " . number_format($validated['actual_cost'], 0, ',', '.') . "]" : ""),
                    ]);
                } else {
                    // Unrepairable: Rusak Total & Nonaktif
                    $unit->update([
                        'condition' => ItemCondition::RUSAK,
                        'status'    => ItemUnitStatus::NONAKTIF,
                        'is_active' => false,
                    ]);

                    if ($detail) {
                        $detail->update([
                            'condition_after' => ItemCondition::RUSAK,
                            'is_returned'     => true,
                            'returned_at'     => $validated['completion_date'],
                        ]);
                    }

                    $lockedTx->update([
                        'status' => TransactionStatus::SELESAI,
                        'notes'  => $lockedTx->notes . " | Dihapusbukukan / Rusak Total: " . $validated['resolution_notes'],
                    ]);
                }
            });

            return redirect()->route('transactions.maintenance.index', ['tab' => 'history'])
                ->with('success', "Proses pemeliharaan aset {$transaction->transaction_code} telah diselesaikan.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Gagal menyelesaikan pemeliharaan: ' . $e->getMessage());
        }
    }

    /**
     * Helper to generate a unique transaction code: PREFIX-YYYYMM-XXXX.
     */
    protected function generateTransactionCode(string $prefix = 'TRX'): string
    {
        $datePrefix = $prefix . '-' . Carbon::now()->format('Ym') . '-';

        $latest = Transaction::where('transaction_code', 'like', $datePrefix . '%')
            ->orderBy('transaction_code', 'desc')
            ->value('transaction_code');

        if ($latest) {
            $numberPart = substr($latest, strlen($datePrefix));
            $nextNumber = ((int) $numberPart) + 1;
        } else {
            $nextNumber = 1;
        }

        return $datePrefix . str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Export transactions data to CSV respecting active filters and user role.
     */
    public function exportCsv(Request $request)
    {
        $user = Auth::user();

        $query = Transaction::with(['catalog.category', 'staff', 'user', 'approver', 'destinationLocation', 'sourceLocation', 'details.item'])
            ->latest('transaction_date')
            ->latest('id');

        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('transaction_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('transaction_date', '<=', $request->end_date);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('transaction_code', 'like', "%{$search}%")
                  ->orWhereHas('staff', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                        ->orWhere('nip', 'like', "%{$search}%");
                  })
                  ->orWhereHas('catalog', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%");
                  });
            });
        }

        $transactions = $query->get();

        $filename = 'riwayat-transaksi-' . date('Ymd-His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'No',
                'Kode Transaksi',
                'Tipe Transaksi',
                'Status',
                'Nama Barang / Katalog',
                'Jumlah Unit',
                'Peminjam / Penanggung Jawab',
                'Lokasi Asal',
                'Lokasi Tujuan',
                'Tanggal Transaksi',
                'Estimasi Batas Pengembalian',
                'Akun Pemohon',
                'Admin Penyetujui',
                'Catatan / Keperluan',
            ]);

            foreach ($transactions as $idx => $tx) {
                fputcsv($file, [
                    $idx + 1,
                    $tx->transaction_code,
                    $tx->type->label(),
                    $tx->status->value ?? $tx->status,
                    $tx->catalog->name ?? ($tx->item->name ?? '-'),
                    $tx->quantity,
                    $tx->staff->name ?? ($tx->to_person ?? '-'),
                    $tx->sourceLocation->name ?? ($tx->from_location ?? '-'),
                    $tx->destinationLocation->name ?? ($tx->to_location ?? '-'),
                    $tx->transaction_date ? $tx->transaction_date->format('Y-m-d') : '-',
                    $tx->expected_return_date ? $tx->expected_return_date->format('Y-m-d') : '-',
                    $tx->user->name ?? '-',
                    $tx->approver->name ?? '-',
                    $tx->notes ?? '-',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
