<?php

declare(strict_types=1);

namespace Vanderb\SluggableRedirect\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Vanderb\SluggableRedirect\Models\SluggableRedirect;

trait SluggableRedirectModel
{
    protected static function bootSluggableRedirectModel(): void
    {
        static::updating(function (Model $model): void {
            if (! $model->isDirty('slug')) {
                return;
            }

            $originalSlug = (string) $model->getOriginal('slug', '');

            if ($originalSlug === '') {
                return;
            }

            $type = $model->getMorphClass();
            $now = $model->freshTimestamp();

            // Atomic insert-or-repoint: a stale redirect for the same slug (e.g. left
            // by another record that used it earlier) now points to this model.
            SluggableRedirect::query()->upsert(
                [[
                    'slug' => $originalSlug,
                    'sluggable_type' => $type,
                    'sluggable_id' => $model->getKey(),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]],
                ['sluggable_type', 'slug'],
                ['sluggable_id', 'updated_at'],
            );

            // The new slug is live now, so a redirect for it would be meaningless.
            SluggableRedirect::query()
                ->where('sluggable_type', $type)
                ->where('slug', (string) $model->getAttribute('slug'))
                ->delete();
        });

        static::deleting(function (Model $model): void {
            // Keep redirects on soft delete so they survive restore().
            if (method_exists($model, 'isForceDeleting') && ! $model->isForceDeleting()) {
                return;
            }

            $model->sluggableRedirects()->delete();
        });
    }

    public function sluggableRedirects(): MorphMany
    {
        return $this->morphMany(SluggableRedirect::class, 'sluggable');
    }
}
