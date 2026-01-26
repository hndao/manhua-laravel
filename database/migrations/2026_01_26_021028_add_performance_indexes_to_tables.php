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
        // Add indexes to comics table (skip if exists)
        if (!$this->indexExists('comics', 'comics_deleted_at_index')) {
            Schema::table('comics', function (Blueprint $table) {
                $table->index('deleted_at', 'comics_deleted_at_index');
            });
        }

        if (!$this->indexExists('comics', 'comics_created_at_index')) {
            Schema::table('comics', function (Blueprint $table) {
                $table->index('created_at', 'comics_created_at_index');
            });
        }

        if (!$this->indexExists('comics', 'comics_status_index')) {
            Schema::table('comics', function (Blueprint $table) {
                $table->index('status', 'comics_status_index');
            });
        }

        if (!$this->indexExists('comics', 'comics_deleted_created_index')) {
            Schema::table('comics', function (Blueprint $table) {
                $table->index(['deleted_at', 'created_at'], 'comics_deleted_created_index');
            });
        }

        // Add indexes to chapters table
        if (!$this->indexExists('chapters', 'chapters_deleted_at_index')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->index('deleted_at', 'chapters_deleted_at_index');
            });
        }

        if (!$this->indexExists('chapters', 'chapters_comic_id_index')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->index('comic_id', 'chapters_comic_id_index');
            });
        }

        if (!$this->indexExists('chapters', 'chapters_comic_deleted_index')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->index(['comic_id', 'deleted_at'], 'chapters_comic_deleted_index');
            });
        }

        // Add indexes to comic_genre table
        if (!$this->indexExists('comic_genre', 'comic_genre_comic_id_index')) {
            Schema::table('comic_genre', function (Blueprint $table) {
                $table->index('comic_id', 'comic_genre_comic_id_index');
            });
        }

        if (!$this->indexExists('comic_genre', 'comic_genre_genre_id_index')) {
            Schema::table('comic_genre', function (Blueprint $table) {
                $table->index('genre_id', 'comic_genre_genre_id_index');
            });
        }

        // Add indexes to author_comic table
        if (!$this->indexExists('author_comic', 'author_comic_comic_id_index')) {
            Schema::table('author_comic', function (Blueprint $table) {
                $table->index('comic_id', 'author_comic_comic_id_index');
            });
        }

        if (!$this->indexExists('author_comic', 'author_comic_author_id_index')) {
            Schema::table('author_comic', function (Blueprint $table) {
                $table->index('author_id', 'author_comic_author_id_index');
            });
        }

        // Add indexes to genres table
        if (!$this->indexExists('genres', 'genres_name_index')) {
            Schema::table('genres', function (Blueprint $table) {
                $table->index('name', 'genres_name_index');
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop indexes from comics table
        Schema::table('comics', function (Blueprint $table) {
            $table->dropIndex('comics_deleted_at_index');
            $table->dropIndex('comics_created_at_index');
            $table->dropIndex('comics_status_index');
            $table->dropIndex('comics_deleted_created_index');
        });

        // Drop indexes from chapters table
        Schema::table('chapters', function (Blueprint $table) {
            $table->dropIndex('chapters_deleted_at_index');
            $table->dropIndex('chapters_comic_id_index');
            $table->dropIndex('chapters_comic_deleted_index');
        });

        // Drop indexes from comic_genre table
        Schema::table('comic_genre', function (Blueprint $table) {
            $table->dropIndex('comic_genre_comic_id_index');
            $table->dropIndex('comic_genre_genre_id_index');
        });

        // Drop indexes from author_comic table
        Schema::table('author_comic', function (Blueprint $table) {
            $table->dropIndex('author_comic_comic_id_index');
            $table->dropIndex('author_comic_author_id_index');
        });

        // Drop indexes from genres table
        Schema::table('genres', function (Blueprint $table) {
            $table->dropIndex('genres_name_index');
        });
    }
};
