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
        Schema::table('office_masters', function (Blueprint $table) {
            $table->foreign(['block_id'], 'block_id_fk')->references(['id'])->on('blocks')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['district_id'], 'district_id_fk')->references(['id'])->on('districts')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['municipalitiy_id'], 'municipalitiy_id_fk')->references(['id'])->on('municipalities')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['parent_id'])->references(['id'])->on('office_masters')->onUpdate('no action')->onDelete('set null');
            $table->foreign(['panchayat_id'], 'panchayat_id_fk')->references(['id'])->on('panchayats')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['state_id'], 'state_id_fk')->references(['id'])->on('states')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['subdivision_id'], 'subdivision_id_fk')->references(['id'])->on('subdivisions')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['ward_id'], 'ward_id_fk')->references(['id'])->on('wards')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('office_masters', function (Blueprint $table) {
            $table->dropForeign('block_id_fk');
            $table->dropForeign('district_id_fk');
            $table->dropForeign('municipalitiy_id_fk');
            $table->dropForeign('office_masters_parent_id_foreign');
            $table->dropForeign('panchayat_id_fk');
            $table->dropForeign('state_id_fk');
            $table->dropForeign('subdivision_id_fk');
            $table->dropForeign('ward_id_fk');
        });
    }
};
