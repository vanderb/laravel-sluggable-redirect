<?php

declare(strict_types=1);

namespace Vanderb\SluggableRedirect\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Vanderb\SluggableRedirect\Traits\SluggableRedirectModel;

class Post extends Model
{
    use SluggableRedirectModel;
    use SoftDeletes;

    protected $guarded = [];
}
