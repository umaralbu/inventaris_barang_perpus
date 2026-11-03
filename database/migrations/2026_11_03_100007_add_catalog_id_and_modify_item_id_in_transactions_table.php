<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['item_id']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('item_id')->nullable()->change();
            $table->foreign('item_id')->references('id')->on('items')->nullOnDelete();

            $table->foreignId('catalog_id')->nullable()->after('transaction_code')->constrained('items_catalog')->nullOnDelete();
            $table->foreignId('to_location_id')->nullable()->after('staff_id')->constrained('locations')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['to_location_id']);
            $table->dropColumn('to_location_id');

            $table->dropForeign(['catalog_id']);
            $table->dropColumn('catalog_id');

            $table->dropForeign(['item_id']);
            $table->unsignedBigInteger('item_id')->nullable(false)->change();
            $table->foreign('item_id')->references('id')->on('items')->cascadeOnDelete();
        });
    }
};
