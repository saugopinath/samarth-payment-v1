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
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE UNIQUE INDEX users_mobile_no_unique_index
        ON public.users USING btree
        (mobile_no ASC NULLS LAST, is_active ASC NULLS LAST)
        TABLESPACE pg_default
        WHERE is_active = 1');  
            DB::statement('CREATE UNIQUE INDEX users_email_unique_index
          ON public.users USING btree
          (email ASC NULLS LAST, is_active ASC NULLS LAST)
          TABLESPACE pg_default
          WHERE is_active = 1');        
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->unique(['mobile_no', 'is_active']);
                $table->unique(['email', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX public.users_mobile_no_unique_index');  
            DB::statement('DROP INDEX public.users_email_unique_index');  
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['mobile_no', 'is_active']);
                $table->dropUnique(['email', 'is_active']);
            });
        }
    }
};
