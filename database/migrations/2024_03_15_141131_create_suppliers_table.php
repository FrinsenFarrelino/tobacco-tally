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
        Schema::create('suppliers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100);
            $table->string('name', 200);
            $table->string('address', 255)->nullable();
            $table->foreignId('subdistrict_id')->nullable()->constrained('subdistricts');
            $table->text('remark')->nullable();
            $table->boolean('is_active')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });

        DB::statement('DROP INDEX IF EXISTS suppliers_code_unique');
        DB::statement('CREATE UNIQUE INDEX suppliers_code_unique ON suppliers (UPPER(code)) WHERE deleted_at IS NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suppliers');
    }
};
