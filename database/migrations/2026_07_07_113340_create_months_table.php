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
        Schema::create('months', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
        });

        // Seed data from Codemaster
        $months = \App\Models\Codemaster::where('parent_short_code', 'lot_month')->get();
        $order = 1;
        foreach ($months as $month) {
            \DB::table('months')->insert([
                'name' => $month->name,
                'code' => $month->code,
                'is_active' => $month->is_active,
                'display_order' => $order++,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('months');
    }
};
