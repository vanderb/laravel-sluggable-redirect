<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Upgrades tables created by older versions, where `slug` was globally unique,
 * to uniqueness per (sluggable_type, slug). A no-op on fresh installs.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasIndex('sluggable_redirects', ['slug'], 'unique')) {
            Schema::table('sluggable_redirects', function (Blueprint $table): void {
                $table->dropUnique(['slug']);
            });
        }

        if (! Schema::hasIndex('sluggable_redirects', ['sluggable_type', 'slug'], 'unique')) {
            Schema::table('sluggable_redirects', function (Blueprint $table): void {
                $table->unique(['sluggable_type', 'slug']);
            });
        }
    }

    public function down(): void
    {
        // Intentionally empty: restoring global uniqueness could fail on valid data.
    }
};
