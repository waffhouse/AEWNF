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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('netsuite_id')->unique()->comment('NetSuite internal ID');
            $table->string('entity_id')->comment('Customer ID/Number in NetSuite');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->text('shipping_address')->nullable();
            $table->string('county')->nullable();
            $table->string('home_state')->nullable();
            $table->string('license_type')->nullable();
            $table->string('license_number')->nullable();
            $table->string('phone')->nullable();
            $table->string('price_level')->nullable();
            $table->string('terms')->nullable();
            $table->timestamp('last_sync_at')->nullable();
            $table->timestamps();
            
            // Add index for faster lookups
            $table->index('entity_id');
            $table->index('home_state');
            $table->index('license_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
