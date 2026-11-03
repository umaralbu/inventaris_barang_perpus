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
        // 1. Tambahkan kolom catalog_id ke tabel items jika belum ada
        if (!Schema::hasColumn('items', 'catalog_id')) {
            Schema::table('items', function (Blueprint $table) {
                $table->unsignedBigInteger('catalog_id')->nullable()->after('id');
            });
        }

        // 2. Data Migration: Kelompokkan data unit di items ke items_catalog
        $items = DB::table('items')->get();

        $catalogs = [];
        foreach ($items as $item) {
            $catId = $item->category_id ?: 2; // Fallback jika category_id kosong ke Elektronik (id 2)
            $key = implode('|||', [
                trim((string) $item->name),
                (string) $catId,
                trim((string) $item->brand),
                trim((string) $item->specification),
                trim((string) $item->unit)
            ]);

            if (!isset($catalogs[$key])) {
                $catalogs[$key] = [
                    'category_id'   => $catId,
                    'name'          => trim((string) $item->name),
                    'brand'         => $item->brand ? trim((string) $item->brand) : null,
                    'specification' => $item->specification ? trim((string) $item->specification) : null,
                    'unit_name'     => $item->unit ? trim((string) $item->unit) : 'unit',
                    'photo'         => $item->photo,
                    'description'   => $item->description,
                    'is_active'     => $item->is_active ?? true,
                    'item_ids'      => []
                ];
            }

            $catalogs[$key]['item_ids'][] = $item->id;
        }

        $now = now();
        foreach ($catalogs as $catData) {
            $catalogId = DB::table('items_catalog')->insertGetId([
                'category_id'   => $catData['category_id'],
                'name'          => $catData['name'],
                'brand'         => $catData['brand'],
                'specification' => $catData['specification'],
                'unit_name'     => $catData['unit_name'],
                'photo'         => $catData['photo'],
                'description'   => $catData['description'],
                'is_active'     => $catData['is_active'],
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);

            DB::table('items')
                ->whereIn('id', $catData['item_ids'])
                ->update(['catalog_id' => $catalogId]);
        }

        // 3. Tambahkan foreign key constraint setelah semua catalog_id terisi
        Schema::table('items', function (Blueprint $table) {
            $table->foreign('catalog_id')->references('id')->on('items_catalog')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('items', 'catalog_id')) {
            Schema::table('items', function (Blueprint $table) {
                try {
                    $table->dropForeign(['catalog_id']);
                } catch (\Exception $e) {
                    // Ignore if foreign key doesn't exist
                }
                $table->dropColumn('catalog_id');
            });
        }
    }
};
