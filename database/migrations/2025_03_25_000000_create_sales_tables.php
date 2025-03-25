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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('tran_id')->unique()->comment('Transaction ID from NetSuite');
            $table->string('type')->comment('Transaction type (Invoice, Credit Memo, etc.)');
            $table->date('date')->comment('Transaction date');
            $table->string('entity_id')->nullable()->comment('Customer entity ID from NetSuite');
            $table->string('customer_name')->nullable()->comment('Customer name');
            $table->decimal('total_amount', 12, 2)->default(0)->comment('Total transaction amount');
            $table->json('raw_data')->nullable()->comment('Raw JSON data from NetSuite');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            
            $table->index('entity_id');
            $table->index('date');
            $table->index('type');
        });

        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained()->onDelete('cascade');
            $table->string('sku')->nullable();
            $table->string('item_description')->nullable();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();
            
            $table->index('sku');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};