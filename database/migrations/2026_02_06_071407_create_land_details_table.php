<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
        Schema::create('pension.land_details', function (Blueprint $table) {

        $table->id();
        $table->unsignedBigInteger('scheme_id');
          $table->unsignedBigInteger('application_id')->unique();
$table->unsignedBigInteger('beneficiary_id')->unique();
                      $table->integer('land')->nullable();
            $table->string('name')->nullable();
            $table->string('mobile')->nullable();
            $table->string('ccc', 10)->nullable();

        $table->jsonb('other_details')->nullable();
        $table->timestamps();
          

        $table->foreign('application_id', 'application_id_fk')
                ->references('application_id')
                ->on('pension.unique_app_ben_ids')
                ->cascadeOnDelete();

        $table->foreign('beneficiary_id', 'beneficiary_id_fk')
                ->references('beneficiary_id')
                ->on('pension.unique_app_ben_ids')
                ->cascadeOnDelete();
        $table->foreign('scheme_id', 'scheme_id_fk')
                ->references('id')
                ->on('public.schemes')
                ->cascadeOnDelete();
        
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pension.land_details');
    }
};