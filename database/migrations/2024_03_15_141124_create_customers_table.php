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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            DB::statement("DROP TYPE IF EXISTS customers_title");
            DB::statement("CREATE TYPE customers_title AS ENUM ('PT', 'CV', '-')");
            $table->enum('title', ['PT', 'CV', '-'])->nullable();
            $table->string('name', 200)->nullable();
            $table->string('address', 255)->nullable();
            $table->foreignId('subdistrict_id')->nullable()->constrained('subdistricts');
            $table->foreignId('sales_id')->nullable()->constrained('employees');
            $table->string('office_phone', 200)->nullable();
            $table->string('fax', 200)->nullable();
            $table->string('email', 200)->nullable();
            $table->string('contact_person', 200)->nullable();
            $table->string('phone_number', 200)->nullable();
            $table->string('send_name', 200)->nullable();
            $table->string('send_address', 255)->nullable();
            $table->foreignId('send_city_id')->nullable()->constrained('cities');
            $table->string('send_phone', 200)->nullable();
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
        Schema::dropIfExists('customers');
    }
};
