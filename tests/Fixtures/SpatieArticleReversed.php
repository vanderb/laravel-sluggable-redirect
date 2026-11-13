<?php

declare(strict_types=1);

namespace Vanderb\SluggableRedirect\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Vanderb\SluggableRedirect\Traits\SluggableRedirectModel;

/** Trait order reversed: the redirect trait boots before HasSlug. */
class SpatieArticleReversed extends Model
{
    use SluggableRedirectModel;
    use HasSlug;

    protected $table = 'articles';

    protected $guarded = [];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
    }
}
