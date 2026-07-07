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
        Schema::create('scheme_attached_doc_mappings', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->smallInteger('scheme_id');
            $table->smallInteger('doc_type_id');
            $table->boolean('is_required');
            $table->string('max_file_size');
            $table->string('extension_type');
            $table->foreign('scheme_id', 'scheme_id_fk')->references('id')->on('schemes');
            $table->foreign('doc_type_id', 'doc_type_id_fk')->references('id')->on('codemasters');
            $table->index('scheme_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheme_attached_doc_mappings');
    }
};
