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
            $table->boolean('open')->default(0);
            $table->boolean('add')->default(0);
            $table->boolean('edit')->default(0);
            $table->boolean('delete')->default(0);
            $table->boolean('print')->default(0);
            $table->boolean('approve')->default(0);
            $table->boolean('disapprove')->default(0);
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
