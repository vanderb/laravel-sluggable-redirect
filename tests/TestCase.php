<?php

declare(strict_types=1);

namespace Vanderb\SluggableRedirect\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Vanderb\SluggableRedirect\SluggableRedirectServiceProvider;
use Vanderb\SluggableRedirect\Tests\Fixtures\PostController;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [SluggableRedirectServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('database.default', 'testing');
    }

    protected function defineDatabaseMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../src/database/migrations');

        Schema::create('posts', function (Blueprint $table): void {
            $table->id();
            $table->string('slug');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('categories', function (Blueprint $table): void {
            $table->id();
            $table->string('slug');
            $table->timestamps();
        });
    }

    protected function defineRoutes($router): void
    {
        Route::get('/posts/{slug}', [PostController::class, 'show'])->name('posts.show');
        Route::get('/categories/{slug}', [PostController::class, 'category'])->name('categories.show');
        Route::get('/unnamed/{slug}', [PostController::class, 'show']);
    }
}
