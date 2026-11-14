<?php

declare(strict_types=1);

namespace Vanderb\SluggableRedirect;

use Illuminate\Support\ServiceProvider;

class SluggableRedirectServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
    }
}
