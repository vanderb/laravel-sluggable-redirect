<?php
namespace Vanderb\SluggableRedirect\Models;

use Illuminate\Database\Eloquent\Model;

class SluggableRedirect extends Model
{
    protected $table = 'sluggable_redirects';

    protected $fillable = ['slug', 'sluggable_id', 'sluggable_type'];

    public function sluggable(){
        return $this->morphTo();
    }
}
