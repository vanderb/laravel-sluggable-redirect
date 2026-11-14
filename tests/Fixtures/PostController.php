<?php

declare(strict_types=1);

namespace Vanderb\SluggableRedirect\Tests\Fixtures;

use Vanderb\SluggableRedirect\Traits\SluggableRedirectHandler;

class PostController
{
    use SluggableRedirectHandler;

    public function show(string $slug, Post $model): mixed
    {
        return $this->checkSlug($slug, $model, fn (Post $post) => 'post:' . $post->id);
    }

    public function category(string $slug, Category $model): mixed
    {
        return $this->checkSlug($slug, $model, fn (Category $c) => 'category:' . $c->id);
    }
}
