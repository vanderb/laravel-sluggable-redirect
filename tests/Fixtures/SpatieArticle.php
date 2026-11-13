<?php

declare(strict_types=1);

namespace Vanderb\SluggableRedirect\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Vanderb\SluggableRedirect\Traits\SluggableRedirectModel;

/** Order as documented in the README: HasSlug first. */
class SpatieArticle extends Model
{
    use HasSlug;
    use SluggableRedirectModel;

    protected $table = 'articles';

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
    }
}
