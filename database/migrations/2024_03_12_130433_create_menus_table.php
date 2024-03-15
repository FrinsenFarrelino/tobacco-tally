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
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->integer('id_menu')->default(0);
            $table->string('code', 100)->unique();
            $table->string('name', 100);
            $table->text('title')->nullable();
            $table->string('type', 100)->nullable();
            $table->text('url_menu')->nullable();
            $table->text('icon')->nullable();
            $table->integer('order')->nullable()->default(0);
            $table->integer('priority')->nullable()->default(0);
            $table->boolean('is_active')->default(1);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->foreignId('updated_by')->nullable()->constrained('users');
            $table->foreignId('deleted_by')->nullable()->constrained('users');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};
