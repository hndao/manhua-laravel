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
        Schema::table('users', function (Blueprint $table) {
            // Social authentication fields
            $table->string('provider')->nullable()->after('password'); // google, facebook, twitter
            $table->string('provider_id')->nullable()->after('provider'); // Social provider user ID
            $table->string('avatar')->nullable()->after('provider_id'); // Profile picture from social

            // Index for social login lookups
            $table->index(['provider', 'provider_id']);
        });

        // Make password nullable for social login users (separate statement to avoid conflicts)
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['provider', 'provider_id', 'avatar']);
            $table->dropIndex(['provider', 'provider_id']);
        });
    }
};
