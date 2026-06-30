<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('pricing_sections', 'credit_info')) {
            return;
        }

        // Ensure `credit_info` is a nullable TEXT column.
        // Implemented with driver-specific SQL instead of Blueprint::change() so the
        // project does not need the doctrine/dbal package (required by ->change() on
        // Laravel 10). On SQLite the column is already created as nullable TEXT and
        // type affinity makes an explicit change unnecessary.
        match (DB::connection()->getDriverName()) {
            'mysql', 'mariadb' => DB::statement('ALTER TABLE `pricing_sections` MODIFY `credit_info` TEXT NULL'),
            'pgsql' => DB::statement('ALTER TABLE "pricing_sections" ALTER COLUMN "credit_info" TYPE TEXT, ALTER COLUMN "credit_info" DROP NOT NULL'),
            default => null,
        };
    }

    public function down(): void
    {
        // No-op: narrowing TEXT back to a shorter column type risks truncating stored
        // content, so the down migration intentionally leaves the column as-is.
    }
};
