# Laravel Sluggable Redirect

Base on laravel-sluggable by spatie

## Setup

### Install

`composer require vanderb/laravel-sluggable-redirect`

### Migrate

Run `php artisan migrate` to migrate the sluggable-redirects table.

## Implement Traits

Implement *SluggableRedirectModel* to model, where you implemented spatie-sluggable.

```
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

use Vanderb\SluggableRedirect\Traits\SluggableRedirectModel;

class YourModel extends Modal {

    use HasSlug, SluggableRedirectModel;

...
```

Implement *SluggableRedirectHandler* to your controller which handles the model in frontend

```
use Vanderb\SluggableRedirect\Traits\SluggableRedirectHandler;

class YourController extends Controlle {

    use SluggableRedirectHandler;
    
}
```

Call the checkSlug-method where you want to check the slug (maybe in show-method):

Parameters:
- $slug = the slug given by method
- $model = model responsable for your slugs, implemted by dependency injection
- callback (do what you normally do in your show method e.g. return view etc.)

```
public function show($slug, Model $model) {
  return $this->checkSlug($slug, $model, function($event) {
    return view('my-view');
  }
}
```

## Notes

Please note, this package is not in stable mode. There are many methods and functions which can be solved better.
If you have any suggestions for that, feel free to make an issue or request. Thank you.
