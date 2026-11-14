<?php

declare(strict_types=1);

namespace Vanderb\SluggableRedirect\Tests\Fixtures;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Vanderb\SluggableRedirect\Traits\SluggableRedirectModel;

class CviebrockArticleReversed extends Model
{
    use SluggableRedirectModel;
    use Sluggable;

    protected $table = 'articles';

    protected $guarded = [];

    public function sluggable(): array
    {
        return ['slug' => ['source' => 'title', 'onUpdate' => true]];
    }
}
