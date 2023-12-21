<?php
namespace Vanderb\SluggableRedirect\Traits;

use Vanderb\SluggableRedirect\Models\SluggableRedirect;

trait SluggableRedirectHandler
{
    public function checkSlug(string $slug, $implementation, $cb) {

        // If model with slug exists return callback (e.g. return view)
        if ($content = $implementation->where('slug', $slug)->first()) {
            return $cb($content);
        }

        // If not search for current slug and redirect
        $route = request()->route()->getName();
        $model = SluggableRedirect::where('slug', $slug)->first();
        if (!is_null($model) && $model->sluggable) {
            return redirect()->route($route, $model->sluggable->slug, 301);
        }

        // If no sluggable exists, return 404
        return abort(404);
    }
}
