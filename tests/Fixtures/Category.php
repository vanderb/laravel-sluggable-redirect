<?php

declare(strict_types=1);

namespace Vanderb\SluggableRedirect\Tests\Fixtures;

use Illuminate\Database\Eloquent\Model;
use Vanderb\SluggableRedirect\Traits\SluggableRedirectModel;

class Category extends Model
{
    use SluggableRedirectModel;

    protected $guarded = [];
}
