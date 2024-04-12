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
        Schema::create('stock_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100);
            $table->date('date');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            $table->boolean('is_approve_1')->default(0);
            $table->foreignId('approved_1_by')->nullable()->constrained('users');
            $table->dateTime('approved_1_at')->nullable();
            $table->boolean('is_approve_2')->default(0);
            $table->foreignId('approved_2_by')->nullable()->constrained('users');
            $table->dateTime('approved_2_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_transfers');
    }
};
