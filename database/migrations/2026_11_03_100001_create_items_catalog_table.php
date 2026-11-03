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
        if (!Schema::hasTable('items_catalog')) {
            Schema::create('items_catalog', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->constrained('categories')->restrictOnDelete();
                $table->string('name');
                $table->string('brand')->nullable();
                $table->text('specification')->nullable();
                $table->string('unit_name', 50)->default('unit');
                $table->string('photo')->nullable();
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items_catalog');
    }
};

