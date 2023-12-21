<?php
namespace Vanderb\SluggableRedirect;

use Illuminate\Support\ServiceProvider;

class SluggableRedirectServiceProvider extends ServiceProvider
{
    public function boot() {
        $this->loadMigrationsFrom(__DIR__.'/database/migrations');
    }
}
