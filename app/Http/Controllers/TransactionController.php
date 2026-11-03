<?php

namespace App\Http\Controllers;

use App\Enums\ItemCondition;
use App\Enums\ItemUnitStatus;
use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Http\Requests\Transaction\ApproveBorrowRequest;
use App\Http\Requests\Transaction\BorrowRequest;
use App\Http\Requests\Transaction\RejectBorrowRequest;
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
     * Display a listing of all transactions (Borrow, Return, etc.).
     * Pegawai only sees their own transactions; Admin sees all.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        $query = Transaction::with(['catalog.category', 'staff', 'user', 'approver', 'destinationLocation', 'details.item'])
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

        // Generate unique transaction code: TRX-YYYYMM-XXXX
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
        // Check authorization: Admin can see any; non-admin can only see their own
        if (!Auth::user()->isAdmin() && $transaction->user_id !== Auth::id()) {
            abort(403, 'Anda tidak memiliki hak akses untuk melihat permohonan ini.');
        }

        $transaction->load([
            'catalog.category',
            'staff',
            'user',
            'approver',
            'destinationLocation',
            'details.item.location',
            'details.fromLocation',
            'details.toLocation',
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

        // Fetch available physical units for this catalog
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
                // Lock transaction row for update
                /** @var Transaction $lockedTransaction */
                $lockedTransaction = Transaction::where('id', $transaction->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($lockedTransaction->status !== TransactionStatus::MENUNGGU_PERSETUJUAN) {
                    throw new \Exception('Permohonan ini telah diproses sebelumnya oleh petugas lain.');
                }

                // Lock selected units for update
                $units = Item::whereIn('id', $validated['unit_ids'])
                    ->lockForUpdate()
                    ->get();

                // Re-verify that each unit is still Available, Good condition, and Active
                foreach ($units as $unit) {
                    if ($unit->status !== ItemUnitStatus::TERSEDIA || $unit->condition !== ItemCondition::BAIK || !$unit->is_active) {
                        throw new \Exception("Unit fisik dengan kode [{$unit->code}] sudah tidak tersedia atau tidak dalam kondisi baik.");
                    }
                }

                if ($units->count() !== (int) $lockedTransaction->quantity) {
                    throw new \Exception('Jumlah unit yang dialokasikan tidak sesuai dengan permohonan.');
                }

                // Create Transaction Details & update unit status
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

                    // Update unit to DIPINJAM and set its current location to destination
                    $unit->update([
                        'status'      => ItemUnitStatus::DIPINJAM,
                        'location_id' => $lockedTransaction->to_location_id ?? $unit->location_id,
                    ]);
                }

                // Update Transaction status to DIPINJAM
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
}

