<?php

declare(strict_types=1);

namespace Vanderb\SluggableRedirect\Traits;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Vanderb\SluggableRedirect\Models\SluggableRedirect;

trait SluggableRedirectHandler
{
    public function checkSlug(string $slug, Model $implementation, Closure $callback): mixed
    {
        $content = $implementation->newQuery()->where('slug', $slug)->first();

        if ($content) {
            return $callback($content);
        }

        $redirect = SluggableRedirect::query()
            ->with('sluggable')
            ->where('sluggable_type', $implementation->getMorphClass())
            ->where('slug', $slug)
            ->first();

        if ($redirect && $redirect->sluggable) {
            return $this->redirectToCurrentSlug((string) $redirect->sluggable->slug);
        }

        throw new NotFoundHttpException();
    }

    protected function redirectToCurrentSlug(string $slug): RedirectResponse
    {
        $route = request()->route();

        if (! $route instanceof Route || ! $routeName = $route->getName()) {
            throw new NotFoundHttpException();
        }

        $parameters = $this->buildRedirectParameters($route, $slug);

        return redirect()->route($routeName, $parameters, 301);
    }

    /**
     * Replace the slug parameter on the current route while keeping the rest intact.
     */
    protected function buildRedirectParameters(Route $route, string $slug): array
    {
        $parameters = $route->parameters();
        $firstParameter = array_key_first($parameters);

        if ($firstParameter === null) {
            $parameterName = $route->parameterNames()[0] ?? 'slug';

            return [$parameterName => $slug];
        }

        $parameters[$firstParameter] = $slug;

        return $parameters;
    }
}
