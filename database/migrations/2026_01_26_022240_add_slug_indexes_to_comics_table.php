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
        // Add index on slug column for faster lookups
        // Slug is already unique, but adding explicit index improves performance
        if (!$this->indexExists('comics', 'comics_slug_index')) {
            Schema::table('comics', function (Blueprint $table) {
                $table->index('slug', 'comics_slug_index');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if ($this->indexExists('comics', 'comics_slug_index')) {
            Schema::table('comics', function (Blueprint $table) {
                $table->dropIndex('comics_slug_index');
            });
        }
    }

    /**
     * Check if an index exists
     */
    private function indexExists(string $table, string $index): bool
    {
        $connection = Schema::getConnection();
        $database = $connection->getDatabaseName();

        // For PostgreSQL
        if ($connection->getDriverName() === 'pgsql') {
            $result = $connection->select(
                "SELECT 1 FROM pg_indexes WHERE tablename = ? AND indexname = ?",
                [$table, $index]
            );
            return count($result) > 0;
        }

        // For MySQL
        $result = $connection->select(
            "SELECT 1 FROM information_schema.statistics
             WHERE table_schema = ? AND table_name = ? AND index_name = ?",
            [$database, $table, $index]
        );

        return count($result) > 0;
    }
};
