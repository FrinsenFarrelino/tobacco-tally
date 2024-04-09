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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->string('name', 200);
            $table->bigInteger('nik')->nullable();
            $table->string('address', 255)->nullable();
            $table->foreignId('subdistrict_id')->nullable()->constrained('subdistricts');
            $table->integer('postal_code')->nullable();
            $table->string('phone_number', 100)->nullable();
            $table->string('mobile_phone_number', 100)->nullable();
            $table->string('whatsapp', 100)->nullable();
            $table->string('email', 200)->nullable();
            $table->string('telegram', 100)->nullable();
            $table->string('skype', 100)->nullable();
            $table->foreignId('division_id')->nullable()->constrained('divisions');
            $table->foreignId('position_id')->nullable()->constrained('positions');
            $table->text('remark')->nullable();
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
        Schema::dropIfExists('employees');
    }
};
