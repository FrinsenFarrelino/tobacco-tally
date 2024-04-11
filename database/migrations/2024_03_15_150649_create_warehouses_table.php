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
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100);
            $table->string('name',200);
            $table->integer('capacity')->nullable();
            $table->foreignId('item_id')->nullable()->constrained('items');
            $table->foreignId('branch_id')->nullable()->constrained('branches');
            $table->text('remark')->nullable();
            $table->bigInteger('stock')->default(0);
            $table->boolean('is_active')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
            $table->dateTime('stock_updated_at')->nullable();
        });

        DB::statement('DROP INDEX IF EXISTS warehouses_code_unique');
        DB::statement('CREATE UNIQUE INDEX warehouses_code_unique ON warehouses (UPPER(code)) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
