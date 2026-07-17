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
        Schema::create('document_type_masters', function (Blueprint $table) {
            $table->id();
            $table->string('document_type_code');
            $table->json('document_mime_type')->nullable(); // To store string array
            $table->json('document_extension')->nullable(); // To store string array
            $table->integer('max_size')->nullable(); // max size in KB or Bytes
            $table->timestamps();
            
            $table->foreign('document_type_code')->references('code')->on('codemasters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_type_masters');
    }
};
