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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('netsuite_id')->unique()->comment('NetSuite internal ID');
            $table->string('sku')->comment('Product SKU from NetSuite');
            $table->string('brand')->nullable();
            $table->string('class')->nullable()->comment('Product category/class');
            $table->string('description')->nullable();
            $table->string('state')->nullable()->comment('State restriction (empty = all states)');
            $table->integer('quantity')->nullable()->comment('Available quantity');
            $table->decimal('fl_price', 10, 2)->nullable()->comment('Florida price');
            $table->decimal('ga_price', 10, 2)->nullable()->comment('Georgia price');
            $table->decimal('bulk_price', 10, 2)->nullable()->comment('Bulk discount price');
            $table->json('raw_data')->nullable()->comment('Full JSON data from NetSuite');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            // Indexes for faster searching
            $table->index('sku');
            $table->index('brand');
            $table->index('class');
            $table->index('state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
