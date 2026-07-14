<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use League\CommonMark\Extension\CommonMark\Node\Inline\Code;
use App\Models\Codemaster;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('office_masters', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('address')->nullable();
            $table->string('zip')->nullable();;
            $table->timestamps();
            $table->smallInteger('office_type_id');
            $table->smallInteger('state_id');
            $table->smallInteger('district_id')->nullable();
            $table->smallInteger('block_id')->nullable();
            $table->Integer('subdivision_id')->nullable();
            $table->Integer('municipalitiy_id')->nullable();
            $table->Integer('ward_id')->nullable();
            $table->Integer('panchayat_id')->nullable();
            $table->foreign('office_type_id', 'office_type_id_fk')->references('code')->on('codemasters')->onDelete('cascade'); 
            $table->foreign('state_id','state_id_fk')->references('id')->on('states')->onDelete('cascade'); 
            $table->foreign('district_id','district_id_fk')->references('id')->on('districts')->onDelete('cascade'); 
            $table->foreign('subdivision_id','subdivision_id_fk')->references('id')->on('subdivisions')->onDelete('cascade'); 
            $table->foreign('municipalitiy_id','municipalitiy_id_fk')->references('id')->on('municipalities')->onDelete('cascade'); 
            $table->foreign('ward_id','ward_id_fk')->references('id')->on('wards')->onDelete('cascade'); 
            $table->foreign('block_id','block_id_fk')->references('id')->on('blocks')->onDelete('cascade'); 
            $table->foreign('panchayat_id','panchayat_id_fk')->references('id')->on('panchayats')->onDelete('cascade'); 
            $table->smallInteger('is_active')->default(1);
            $table->index('id');
            $table->index('district_id');
            $table->index('subdivision_id');
            $table->index('municipalitiy_id');
            $table->index('ward_id');
            $table->index('block_id');
            $table->index('panchayat_id');
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
