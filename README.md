# Laravel Sluggable Redirect

**Laravel Sluggable Redirect** creates sluggable history and redirects to current slug. Compatible with [spatie/laravel-sluggable](https://github.com/spatie/laravel-sluggable) and [cviebrock/eloquent-sluggable](https://github.com/cviebrock/eloquent-sluggable). Originally [created](https://github.com/vanderb/laravel-sluggable-redirect) by [Freerk van Zeijl](https://github.com/vanderb), updated by [Viacheslav Radionov](https://github.com/Slaver).

## Setup

### Install

`composer require slaver/laravel-sluggable-redirect`

### Migrating from `vanderb/laravel-sluggable-redirect`

This is the maintained continuation of the original package (which has not been updated since 2023). The PHP namespace (`Vanderb\SluggableRedirect`) and the table are unchanged, so only Composer needs to be told:

```
composer remove vanderb/laravel-sluggable-redirect
composer require slaver/laravel-sluggable-redirect
php artisan migrate
```

The package `replace`s the original one, so the two cannot be installed together.

### Migrate

Run `php artisan migrate` to migrate the sluggable-redirects table.

Requires PHP 8.2+ and Laravel 12 or 13 (Laravel 13 itself needs PHP 8.3+). Redirects are unique per `(sluggable_type, slug)`; when upgrading from an older version, the included migration converts the old global unique index automatically.

The order of the traits on the model does not matter. Integration with `spatie/laravel-sluggable` (3.8–6) and `cviebrock/eloquent-sluggable` (12–13) is covered by tests. Note that cviebrock only regenerates a slug on update with `'onUpdate' => true` in `sluggable()`.

Redirects survive soft deletes and are removed on force delete.

## Tests

`composer install && composer test`

## Implement Traits

Implement *SluggableRedirectModel* to model, where you implemented sluggable package.

```php
use Illuminate\Database\Eloquent\Model;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
// or use Cviebrock\EloquentSluggable\Sluggable;

use Vanderb\SluggableRedirect\Traits\SluggableRedirectModel;

class YourModel extends Model {

    use HasSlug;
	// or use Sluggable;
	use SluggableRedirectModel;

...
```

Implement *SluggableRedirectHandler* to your controller which handles the model in frontend

```php
use Vanderb\SluggableRedirect\Traits\SluggableRedirectHandler;

class YourController extends Controller {

    use SluggableRedirectHandler;
    
}
```

Call the checkSlug-method where you want to check the slug (maybe in show-method):

Parameters:
- $slug = the slug given by method
- $model = a concrete model class responsible for your slugs, resolved by dependency injection (not the abstract `Model`)
- callback (do what you normally do in your show method e.g. return view etc.)

```php
public function show(string $slug, YourModel $model) {
    return $this->checkSlug($slug, $model, function (YourModel $item) {
        return view('my-view', ['item' => $item]);
    });
}
```

## Notes

Please note, this package is not in stable mode. There are many methods and functions which can be solved better.
If you have any suggestions for that, feel free to make an issue or request. Thank you.
