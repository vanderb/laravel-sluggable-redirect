<?php
namespace Vanderb\SluggableRedirect\Traits;

use Vanderb\SluggableRedirect\Models\SluggableRedirect;

trait SluggableRedirectModel
{
    public static function boot() {

        if (!static::getEventDispatcher()){
            static::setEventDispatcher( new \Illuminate\Events\Dispatcher());
        }

        parent::boot();

        static::updated(function($model) {
            if ($model->isDirty('slug')){
                // Get original Data
                $original = $model->getOriginal();

                if (!empty($original['slug'])) {
                    $model->sluggable()->create( [
                        'slug' => $original['slug']
                    ] );
                }
            }
        });

        static::deleting(function($model) {
            $model->sluggable()->where('sluggable_id', $model->id)->delete();
        });
    }

    public function sluggable() {
        return $this->morphMany(SluggableRedirect::class, 'sluggable');
    }
}
