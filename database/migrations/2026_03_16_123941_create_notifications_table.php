<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->string('title', 255);
            $table->text('message');

            $table->string('scheme_name', 255)->nullable();

            $table->string('type', 255);
            $table->string('status', 255);

            $table->json('meta')->nullable();

            $table->timestamp('notified_at');

            $table->timestamps();

            $table->index('type');
            $table->index('status');
            $table->index('notified_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};