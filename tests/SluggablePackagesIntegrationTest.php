<?php

declare(strict_types=1);

namespace Vanderb\SluggableRedirect\Tests;

use Cviebrock\EloquentSluggable\ServiceProvider as CviebrockProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Sluggable\SluggableServiceProvider;
use Vanderb\SluggableRedirect\Models\SluggableRedirect;
use Vanderb\SluggableRedirect\Tests\Fixtures\CviebrockArticle;
use Vanderb\SluggableRedirect\Tests\Fixtures\CviebrockArticleReversed;
use Vanderb\SluggableRedirect\Tests\Fixtures\SpatieArticle;
use Vanderb\SluggableRedirect\Tests\Fixtures\SpatieArticleReversed;

class SluggablePackagesIntegrationTest extends TestCase
{
    protected function getPackageProviders($app): array
    {
        $providers = [...parent::getPackageProviders($app), CviebrockProvider::class];

        // spatie/laravel-sluggable < 4 has no service provider
        if (class_exists(SluggableServiceProvider::class)) {
            $providers[] = SluggableServiceProvider::class;
        }

        return $providers;
    }

    protected function defineDatabaseMigrations(): void
    {
        parent::defineDatabaseMigrations();

        Schema::create('articles', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('slug');
            $table->timestamps();
        });
    }

    public static function models(): array
    {
        return [
            'spatie' => [SpatieArticle::class],
            'spatie, trait order reversed' => [SpatieArticleReversed::class],
            'cviebrock' => [CviebrockArticle::class],
            'cviebrock, trait order reversed' => [CviebrockArticleReversed::class],
        ];
    }

    /** @param class-string<Model> $class */
    #[DataProvider('models')]
    public function test_slug_is_generated_and_no_redirect_on_create(string $class): void
    {
        $article = $class::create(['title' => 'Hello World']);

        $this->assertSame('hello-world', $article->slug);
        $this->assertSame(0, SluggableRedirect::count());
    }

    /** @param class-string<Model> $class */
    #[DataProvider('models')]
    public function test_title_change_regenerates_slug_and_creates_redirect(string $class): void
    {
        $article = $class::create(['title' => 'Hello World']);
        $article->update(['title' => 'Goodbye World']);

        $this->assertSame('goodbye-world', $article->fresh()->slug);
        $this->assertDatabaseHas('sluggable_redirects', [
            'slug' => 'hello-world',
            'sluggable_type' => $article->getMorphClass(),
            'sluggable_id' => $article->id,
        ]);
    }

    /** @param class-string<Model> $class */
    #[DataProvider('models')]
    public function test_chain_of_title_changes_keeps_every_old_slug(string $class): void
    {
        $article = $class::create(['title' => 'One']);
        $article->update(['title' => 'Two']);
        $article->update(['title' => 'Three']);

        $this->assertEqualsCanonicalizing(
            ['one', 'two'],
            SluggableRedirect::pluck('slug')->all(),
        );
    }

    /** @param class-string<Model> $class */
    #[DataProvider('models')]
    public function test_unrelated_update_creates_no_redirect(string $class): void
    {
        $article = $class::create(['title' => 'Hello World']);
        $article->touch();

        $this->assertSame(0, SluggableRedirect::count());
    }
}
