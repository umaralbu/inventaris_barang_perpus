<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Modifikasi tabel transactions
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'transaction_code')) {
                $table->string('transaction_code', 50)->nullable()->unique()->after('id');
            }
            if (!Schema::hasColumn('transactions', 'staff_id')) {
                $table->foreignId('staff_id')->nullable()->after('status')->constrained('staff')->nullOnDelete();
            }
            if (!Schema::hasColumn('transactions', 'expected_return_date')) {
                $table->date('expected_return_date')->nullable()->after('transaction_date');
            }
            if (!Schema::hasColumn('transactions', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('notes');
            }
        });

        // 2. Buat tabel transaction_details
        if (!Schema::hasTable('transaction_details')) {
            Schema::create('transaction_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('transaction_id')->constrained('transactions')->cascadeOnDelete();
                $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
                $table->foreignId('from_location_id')->nullable()->constrained('locations')->nullOnDelete();
                $table->foreignId('to_location_id')->nullable()->constrained('locations')->nullOnDelete();
                $table->string('condition_before', 50)->default('Baik');
                $table->string('condition_after', 50)->nullable();
                $table->boolean('is_returned')->default(false);
                $table->timestamp('returned_at')->nullable();
                $table->timestamps();
            });
        }

        // 3. Sinkronisasi data riil transaksi lama (jika ada)
        $existingTransactions = DB::table('transactions')->get();
        $staffMap = DB::table('staff')->pluck('id', 'name')->toArray();

        foreach ($existingTransactions as $trx) {
            // Generate transaction_code jika belum terisi
            $code = $trx->transaction_code ?: ('TRX-' . date('Ym', strtotime($trx->transaction_date ?: $trx->created_at ?: now())) . '-' . str_pad($trx->id, 4, '0', STR_PAD_LEFT));

            // Cari staff_id berdasarkan to_person atau from_person
            $matchedStaffId = $trx->staff_id;
            if (!$matchedStaffId) {
                if (!empty($trx->to_person) && isset($staffMap[$trx->to_person])) {
                    $matchedStaffId = $staffMap[$trx->to_person];
                } elseif (!empty($trx->from_person) && isset($staffMap[$trx->from_person])) {
                    $matchedStaffId = $staffMap[$trx->from_person];
                }
            }

            DB::table('transactions')
                ->where('id', $trx->id)
                ->update([
                    'transaction_code' => $code,
                    'staff_id'         => $matchedStaffId,
                ]);

            // Migrasikan item_id transaksi lama ke transaction_details jika ada item_id
            if (!empty($trx->item_id)) {
                $alreadyExists = DB::table('transaction_details')
                    ->where('transaction_id', $trx->id)
                    ->where('item_id', $trx->item_id)
                    ->exists();

                if (!$alreadyExists) {
                    $isReturned = in_array($trx->status, ['Dikembalikan', 'Selesai']);
                    DB::table('transaction_details')->insert([
                        'transaction_id'    => $trx->id,
                        'item_id'           => $trx->item_id,
                        'condition_before'  => $trx->condition ?: 'Baik',
                        'condition_after'   => $isReturned ? ($trx->condition ?: 'Baik') : null,
                        'is_returned'       => $isReturned,
                        'returned_at'       => $isReturned ? ($trx->updated_at ?: now()) : null,
                        'created_at'        => $trx->created_at ?: now(),
                        'updated_at'        => $trx->updated_at ?: now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');

        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'staff_id')) {
                $table->dropForeign(['staff_id']);
                $table->dropColumn('staff_id');
            }
            if (Schema::hasColumn('transactions', 'transaction_code')) {
                $table->dropColumn('transaction_code');
            }
            if (Schema::hasColumn('transactions', 'expected_return_date')) {
                $table->dropColumn('expected_return_date');
            }
            if (Schema::hasColumn('transactions', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
        });
    }
};

