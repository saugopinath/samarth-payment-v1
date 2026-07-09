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
        Schema::create('office_masters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('zip')->nullable();
            $table->timestamps();
            $table->smallInteger('office_type_id');
            $table->smallInteger('state_id');
            $table->smallInteger('district_id')->nullable()->index();
            $table->smallInteger('block_id')->nullable()->index();
            $table->integer('subdivision_id')->nullable()->index();
            $table->integer('municipalitiy_id')->nullable()->index();
            $table->integer('ward_id')->nullable()->index();
            $table->integer('panchayat_id')->nullable()->index();
            $table->smallInteger('is_active')->default(1);
            $table->integer('max_operator')->nullable();
            $table->integer('max_verifier')->nullable();
            $table->integer('max_enquiry_officer')->nullable();
            $table->bigInteger('parent_id')->nullable();

            $table->index(['id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('office_masters');
    }
};
