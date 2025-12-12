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
        Schema::create('chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('comic_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->integer('chapter_number');
            $table->integer('volume_number')->nullable();
            $table->integer('total_pages')->default(0);
            $table->integer('views')->default(0);
            $table->date('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['comic_id', 'chapter_number']);
            $table->index(['comic_id', 'chapter_number']);
            $table->index('published_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
