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
        Schema::create('access_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_group_id')->nullable()->constrained('user_groups');
            $table->foreignId('menu_id')->nullable()->constrained('menus');
            $table->boolean('open');
            $table->boolean('add');
            $table->boolean('edit');
            $table->boolean('delete');
            $table->boolean('print');
            $table->boolean('approve');
            $table->boolean('disapprove');
            $table->boolean('is_active')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('access_menus');
    }
};
