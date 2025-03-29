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
        Schema::table('sale_items', function (Blueprint $table) {
            $table->string('class')->nullable()->comment('Item class from NetSuite');
            $table->string('brand')->nullable()->comment('Item brand from NetSuite (custitem_brand)');
            
            // Add indexes for filtering by class and brand
            $table->index('class');
            $table->index('brand');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropIndex(['class']);
            $table->dropIndex(['brand']);
            $table->dropColumn(['class', 'brand']);
        });
    }
};
