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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100);
            $table->string('name', 200);
            $table->foreignId('category_id')->nullable()->constrained('categories');
            $table->foreignId('unit_id')->nullable()->constrained('units');
            $table->bigInteger('buy_price');
            $table->bigInteger('sell_price');
            $table->text('remark')->nullable();
            $table->boolean('is_active')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('DROP INDEX IF EXISTS items_code_unique');
        DB::statement('CREATE UNIQUE INDEX items_code_unique ON items (UPPER(code)) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
