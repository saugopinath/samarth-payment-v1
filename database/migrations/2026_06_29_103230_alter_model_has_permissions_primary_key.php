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
        // Drop the primary key which is preventing multiple schemes
        DB::statement('ALTER TABLE model_has_permissions DROP CONSTRAINT IF EXISTS model_has_permissions_pkey');
        DB::statement('ALTER TABLE model_has_permissions DROP CONSTRAINT IF EXISTS model_has_permissions_permission_model_type_primary');
        
        // Add a unique index that includes scheme_id (treating nulls as 0 for uniqueness)
        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS model_has_permissions_unique ON model_has_permissions (permission_id, model_id, model_type, COALESCE(scheme_id, 0))');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS model_has_permissions_unique');
        // Warning: This down migration might fail if there are duplicates when restoring the PK.
        DB::statement('ALTER TABLE model_has_permissions ADD PRIMARY KEY (permission_id, model_id, model_type)');
    }
};
