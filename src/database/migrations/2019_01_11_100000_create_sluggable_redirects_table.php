<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sluggable_redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('slug');
            $table->morphs('sluggable');
            $table->timestamps();

            $table->unique(['sluggable_type', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sluggable_redirects');
    }
};
