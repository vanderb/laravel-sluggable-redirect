<?php

declare(strict_types=1);

namespace Vanderb\SluggableRedirect\Tests;

use Illuminate\Support\Facades\Schema;
use Vanderb\SluggableRedirect\Models\SluggableRedirect;
use Vanderb\SluggableRedirect\Tests\Fixtures\Category;
use Vanderb\SluggableRedirect\Tests\Fixtures\Post;

class SluggableRedirectTest extends TestCase
{
    public function test_no_redirect_on_create_or_unchanged_slug(): void
    {
        $post = Post::create(['slug' => 'a']);
        $post->update(['slug' => 'a']);

        $this->assertSame(0, SluggableRedirect::count());
    }

    public function test_redirect_created_when_slug_changes(): void
    {
        $post = Post::create(['slug' => 'a']);
        $post->update(['slug' => 'b']);

        $this->assertDatabaseHas('sluggable_redirects', [
            'slug' => 'a',
            'sluggable_type' => $post->getMorphClass(),
            'sluggable_id' => $post->id,
        ]);
    }

    public function test_chain_a_b_c_redirects_all_old_slugs_to_current(): void
    {
        $post = Post::create(['slug' => 'a']);
        $post->update(['slug' => 'b']);
        $post->update(['slug' => 'c']);

        $this->assertSame(2, SluggableRedirect::count());
        $this->get('/posts/a')->assertRedirect('/posts/c')->assertStatus(301);
        $this->get('/posts/b')->assertRedirect('/posts/c')->assertStatus(301);
        $this->get('/posts/c')->assertOk()->assertSee('post:' . $post->id);
    }

    public function test_returning_to_old_slug_removes_its_redirect(): void
    {
        $post = Post::create(['slug' => 'a']);
        $post->update(['slug' => 'b']);
        $post->update(['slug' => 'a']);

        $this->assertDatabaseMissing('sluggable_redirects', ['slug' => 'a']);
        $this->assertDatabaseHas('sluggable_redirects', ['slug' => 'b']);
        $this->get('/posts/a')->assertOk();
        $this->get('/posts/b')->assertRedirect('/posts/a');
    }

    public function test_same_old_slug_in_different_models_types_keeps_both_redirects(): void
    {
        $post = Post::create(['slug' => 'news']);
        $category = Category::create(['slug' => 'news']);

        $post->update(['slug' => 'post-news']);
        $category->update(['slug' => 'cat-news']);

        $this->assertSame(2, SluggableRedirect::where('slug', 'news')->count());
        $this->get('/posts/news')->assertRedirect('/posts/post-news');
        $this->get('/categories/news')->assertRedirect('/categories/cat-news');
    }

    public function test_redirect_does_not_leak_across_model_types(): void
    {
        $post = Post::create(['slug' => 'only-post']);
        $post->update(['slug' => 'renamed']);

        $this->get('/categories/only-post')->assertNotFound();
    }

    public function test_stale_redirect_is_repointed_to_new_owner(): void
    {
        $first = Post::create(['slug' => 'a']);
        $first->update(['slug' => 'b']);          // a -> first

        $second = Post::create(['slug' => 'a']);  // slug a reused by another post
        $second->update(['slug' => 'c']);         // a must now point to second

        $this->assertSame(1, SluggableRedirect::where('slug', 'a')->count());
        $this->assertDatabaseHas('sluggable_redirects', [
            'slug' => 'a',
            'sluggable_id' => $second->id,
        ]);
        $this->get('/posts/a')->assertRedirect('/posts/c');
    }

    public function test_repeated_save_of_same_transition_does_not_violate_unique(): void
    {
        $post = Post::create(['slug' => 'a']);
        $post->update(['slug' => 'b']);
        // simulate a stale concurrent writer that still holds the original slug
        Post::query()->whereKey($post->id)->toBase()->update(['slug' => 'a']);
        $stale = Post::find($post->id);
        $stale->update(['slug' => 'z']);

        $this->assertSame(1, SluggableRedirect::where('slug', 'a')->count());
    }

    public function test_soft_delete_keeps_redirects_and_restore_works(): void
    {
        $post = Post::create(['slug' => 'a']);
        $post->update(['slug' => 'b']);

        $post->delete();
        $this->assertSame(1, SluggableRedirect::count());
        $this->get('/posts/a')->assertNotFound(); // soft-deleted target

        $post->restore();
        $this->get('/posts/a')->assertRedirect('/posts/b');
    }

    public function test_force_delete_removes_redirects(): void
    {
        $post = Post::create(['slug' => 'a']);
        $post->update(['slug' => 'b']);

        $post->forceDelete();

        $this->assertSame(0, SluggableRedirect::count());
    }

    public function test_hard_delete_on_model_without_soft_deletes_removes_redirects(): void
    {
        $category = Category::create(['slug' => 'a']);
        $category->update(['slug' => 'b']);

        $category->delete();

        $this->assertSame(0, SluggableRedirect::count());
    }

    public function test_unknown_slug_returns_404(): void
    {
        $this->get('/posts/nope')->assertNotFound();
    }

    public function test_unnamed_route_returns_404_for_redirect(): void
    {
        $post = Post::create(['slug' => 'a']);
        $post->update(['slug' => 'b']);

        $this->get('/unnamed/a')->assertNotFound();
    }

    public function test_unique_index_is_per_type_and_slug(): void
    {
        $this->assertTrue(Schema::hasIndex('sluggable_redirects', ['sluggable_type', 'slug'], 'unique'));
        $this->assertFalse(Schema::hasIndex('sluggable_redirects', ['slug'], 'unique'));
    }

    public function test_upgrade_migration_converts_legacy_global_unique_index(): void
    {
        Schema::drop('sluggable_redirects');
        Schema::create('sluggable_redirects', function ($table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->morphs('sluggable');
            $table->timestamps();
        });

        $migration = require __DIR__ . '/../src/database/migrations/2026_11_13_100000_make_sluggable_redirects_slug_unique_per_type.php';
        $migration->up();
        $migration->up(); // idempotent

        $this->test_unique_index_is_per_type_and_slug();
    }
}
