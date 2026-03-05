<?php

namespace BeegoodIT\FilamentSocialGraph\Tests;

use BeegoodIT\FilamentSocialGraph\Http\Controllers\FeedController;
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class FeedItemBodyDisplayTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        config()->set('filament-social-graph.actor_models', [TestUser::class]);

        Route::model('entity', TestUser::class);
        Route::middleware('web')->group(function (): void {
            Route::get('feed/{entity}', [FeedController::class, 'index'])->name('feed.index');
            Route::post('feed/{entity}', [FeedController::class, 'store'])->name('feed.store');
        });
    }

    public function test_feed_item_card_sanitizes_html_body_and_removes_script_tags(): void
    {
        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'actor_type' => TestUser::class,
            'actor_id' => $user->getKey(),
            'body' => '<p>Safe text</p><script>alert("evil")</script><strong>bold</strong>',
        ]);

        $html = View::make('filament-social-graph::livewire.feed-item-card', ['feedItem' => $feedItem])->render();

        $this->assertStringContainsString('Safe text', $html);
        $this->assertStringContainsString('<strong>bold</strong>', $html);
        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringNotContainsString('alert("evil")', $html);
    }

    public function test_feed_item_card_renders_markdown_when_body_has_no_html_tags(): void
    {
        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $feedItem = FeedItem::create([
            'actor_type' => TestUser::class,
            'actor_id' => $user->getKey(),
            'body' => '**Bold** and _italic_',
        ]);

        $html = View::make('filament-social-graph::livewire.feed-item-card', ['feedItem' => $feedItem])->render();

        $this->assertStringContainsString('Bold', $html);
        $this->assertStringContainsString('italic', $html);
        $this->assertMatchesRegularExpression('/<strong>.*Bold.*<\/strong>/', $html);
    }

    public function test_store_accepts_html_body_and_index_renders_sanitized(): void
    {
        if (! class_exists(\Lorisleiva\Actions\Concerns\AsAction::class)) {
            $this->markTestSkipped('Requires lorisleiva/laravel-actions (run from app that has the package).');
        }

        $user = TestUser::create([
            'name' => 'Feed Owner',
            'email' => 'owner@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($user);

        $htmlBody = '<p>Posted via form</p><script>document.steal()</script>';
        $response = $this->post(route('feed.store', ['entity' => $user->getKey()]), [
            'body' => $htmlBody,
        ]);

        $response->assertRedirect();
        $item = FeedItem::first();
        $this->assertSame($htmlBody, $item->body);

        $indexResponse = $this->get(route('feed.index', ['entity' => $user->getKey()]));
        $indexResponse->assertOk();
        $indexResponse->assertSee('Posted via form', false);
        $indexResponse->assertDontSee('<script>', false);
        $indexResponse->assertDontSee('document.steal()', false);
    }
}
