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
        Schema::create('financial_years', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->integer('code')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed data from Codemaster
        $years = \App\Models\Codemaster::where('parent_short_code', 'financial_year')->get();
        foreach ($years as $year) {
            \DB::table('financial_years')->insert([
                'name' => $year->name,
                'code' => $year->code,
                'is_active' => $year->is_active,
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
        Schema::dropIfExists('financial_years');
    }
};
