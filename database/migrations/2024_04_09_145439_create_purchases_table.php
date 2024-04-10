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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100);
            $table->date('date');
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers');
            $table->text('remark')->nullable();
            $table->string('subtotal', 255)->nullable();
            $table->integer('ppn')->nullable();
            $table->string('ppn_price', 255)->nullable();
            $table->string('total', 255)->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            $table->boolean('is_approve')->default(0);
            $table->foreignId('approved_by')->nullable()->constrained('users');
            $table->dateTime('approved_at')->nullable();
        });

        DB::statement('DROP INDEX IF EXISTS purchases_code_unique');
        DB::statement('CREATE UNIQUE INDEX purchases_code_unique ON purchases (UPPER(code)) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
