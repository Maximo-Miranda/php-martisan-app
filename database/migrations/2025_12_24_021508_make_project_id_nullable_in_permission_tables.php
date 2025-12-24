<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Makes project_id nullable in permission pivot tables to support global roles.
     * Global roles (like Super Admin) have project_id = NULL and apply across all projects.
     *
     * Since project_id is part of the primary key, we need to:
     * 1. Drop the existing primary key constraint
     * 2. Make project_id nullable
     * 3. Create a new unique index instead (allows NULLs)
     */
    public function up(): void
    {
        $teamForeignKey = config('permission.column_names.team_foreign_key');

        // model_has_roles: drop PK, make nullable, add unique index
        $this->dropPrimaryKey('model_has_roles');
        DB::statement("ALTER TABLE model_has_roles ALTER COLUMN {$teamForeignKey} DROP NOT NULL");
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS model_has_roles_unique ON model_has_roles ({$teamForeignKey}, role_id, model_id, model_type) WHERE {$teamForeignKey} IS NOT NULL");
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS model_has_roles_global_unique ON model_has_roles (role_id, model_id, model_type) WHERE {$teamForeignKey} IS NULL");

        // model_has_permissions: drop PK, make nullable, add unique index
        $this->dropPrimaryKey('model_has_permissions');
        DB::statement("ALTER TABLE model_has_permissions ALTER COLUMN {$teamForeignKey} DROP NOT NULL");
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS model_has_permissions_unique ON model_has_permissions ({$teamForeignKey}, permission_id, model_id, model_type) WHERE {$teamForeignKey} IS NOT NULL");
        DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS model_has_permissions_global_unique ON model_has_permissions (permission_id, model_id, model_type) WHERE {$teamForeignKey} IS NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $teamForeignKey = config('permission.column_names.team_foreign_key');

        // model_has_roles: restore
        DB::statement('DROP INDEX IF EXISTS model_has_roles_unique');
        DB::statement('DROP INDEX IF EXISTS model_has_roles_global_unique');
        DB::statement("DELETE FROM model_has_roles WHERE {$teamForeignKey} IS NULL");
        DB::statement("ALTER TABLE model_has_roles ALTER COLUMN {$teamForeignKey} SET NOT NULL");
        DB::statement("ALTER TABLE model_has_roles ADD PRIMARY KEY ({$teamForeignKey}, role_id, model_id, model_type)");

        // model_has_permissions: restore
        DB::statement('DROP INDEX IF EXISTS model_has_permissions_unique');
        DB::statement('DROP INDEX IF EXISTS model_has_permissions_global_unique');
        DB::statement("DELETE FROM model_has_permissions WHERE {$teamForeignKey} IS NULL");
        DB::statement("ALTER TABLE model_has_permissions ALTER COLUMN {$teamForeignKey} SET NOT NULL");
        DB::statement("ALTER TABLE model_has_permissions ADD PRIMARY KEY ({$teamForeignKey}, permission_id, model_id, model_type)");
    }

    /**
     * Drop the primary key constraint from a table.
     */
    private function dropPrimaryKey(string $table): void
    {
        $constraintName = DB::selectOne("
            SELECT conname
            FROM pg_constraint
            WHERE conrelid = '{$table}'::regclass
            AND contype = 'p'
        ");

        if ($constraintName) {
            DB::statement("ALTER TABLE {$table} DROP CONSTRAINT {$constraintName->conname}");
        }
    }
};
