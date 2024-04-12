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
        Schema::create('stock_transfer_item_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_transfer_id')->nullable()->constrained('stock_transfers');
            $table->foreignId('item_id')->nullable()->constrained('items');
            $table->bigInteger('amount')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfer_item_details');
    }
};
