<?php

declare(strict_types=1);

namespace Vanderb\SluggableRedirect\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SluggableRedirect extends Model
{
    protected $table = 'sluggable_redirects';

    protected $fillable = [
        'slug',
        'sluggable_id',
        'sluggable_type',
    ];

    public function sluggable(): MorphTo
    {
        return $this->morphTo();
    }
}
