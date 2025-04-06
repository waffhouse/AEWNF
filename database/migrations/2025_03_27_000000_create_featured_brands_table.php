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
        Schema::create('featured_brands', function (Blueprint $table) {
            $table->id();
            $table->string('brand')->unique()->comment('Brand name to be featured');
            $table->integer('display_order')->default(1)->comment('Order to display brands (lower numbers first)');
            $table->boolean('is_active')->default(true)->comment('Whether this brand is active');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            // Indexes
            $table->index('display_order');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('featured_brands');
    }
};
