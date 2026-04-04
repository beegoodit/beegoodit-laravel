<?php

namespace BeegoodIT\FilamentEntryLinks\Tests\Feature;

use BeegoodIT\FilamentEntryLinks\Enums\EntryLinkRedirectCode;
use BeegoodIT\FilamentEntryLinks\Events\EntryLinkFollowed;
use BeegoodIT\FilamentEntryLinks\Models\EntryLink;
use BeegoodIT\FilamentEntryLinks\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class EntryLinkRedirectTest extends TestCase
{
    public function test_redirects_with_302_by_default(): void
    {
        $link = EntryLink::factory()->create([
            'token' => 'abc12345',
            'target_url' => 'https://example.com/landing',
            'redirect_code' => EntryLinkRedirectCode::Temporary,
        ]);

        $response = $this->get('/link/'.$link->token.'-poster');

        $response->assertRedirect('https://example.com/landing');
        $response->assertStatus(302);
    }

    public function test_redirects_with_301_when_configured(): void
    {
        $link = EntryLink::factory()->create([
            'token' => 'perm1234',
            'target_url' => 'https://example.com/permanent',
            'redirect_code' => EntryLinkRedirectCode::Permanent,
        ]);

        $response = $this->get('/link/'.$link->token);

        $response->assertRedirect('https://example.com/permanent');
        $response->assertStatus(301);
    }

    public function test_ignores_cosmetic_slug_when_resolving_token(): void
    {
        $link = EntryLink::factory()->create([
            'token' => 'tokx',
            'target_url' => 'https://example.com/here',
        ]);

        $this->get('/link/tokx-completely-different-slug')
            ->assertRedirect('https://example.com/here');
    }

    public function test_returns_404_for_unknown_token(): void
    {
        $this->get('/link/notfound-here')->assertNotFound();
    }

    public function test_returns_404_when_disabled(): void
    {
        $link = EntryLink::factory()->disabled()->create([
            'token' => 'disabled1',
            'target_url' => 'https://example.com/nope',
        ]);

        $this->get('/link/'.$link->token)->assertNotFound();
    }

    public function test_returns_404_when_expired(): void
    {
        $link = EntryLink::factory()->expired()->create([
            'token' => 'expired1',
            'target_url' => 'https://example.com/old',
        ]);

        $this->get('/link/'.$link->token)->assertNotFound();
    }

    public function test_returns_404_when_target_host_not_allowed_in_same_app_mode(): void
    {
        $this->app['config']->set('filament-entry-links.allowed_url_mode', 'same_app');

        $link = EntryLink::factory()->create([
            'token' => 'evilhost',
            'target_url' => 'https://malicious.example/hack',
        ]);

        $this->get('/link/'.$link->token)->assertNotFound();
    }

    public function test_allows_external_url_when_allowlist_mode_includes_host(): void
    {
        $this->app['config']->set('filament-entry-links.allowed_url_mode', 'allowlist');
        $this->app['config']->set('filament-entry-links.allowed_hosts', ['partner.test']);

        $link = EntryLink::factory()->create([
            'token' => 'partner',
            'target_url' => 'https://partner.test/campaign',
        ]);

        $this->get('/link/'.$link->token)
            ->assertRedirect('https://partner.test/campaign');
    }

    public function test_returns_200_coming_soon_before_active_from(): void
    {
        $link = EntryLink::factory()->scheduled(now()->addDays(3))->create([
            'token' => 'future1',
            'target_url' => 'https://example.com/future',
        ]);

        $response = $this->get('/link/'.$link->token);

        $response->assertOk();
        $response->assertSee(__('filament-entry-links::public.coming_soon_heading'), false);
    }

    public function test_dispatches_entry_link_followed_on_successful_redirect(): void
    {
        Event::fake([EntryLinkFollowed::class]);

        $link = EntryLink::factory()->create([
            'token' => 'eventtok',
            'target_url' => 'https://example.com/track',
        ]);

        $this->get('/link/'.$link->token);

        Event::assertDispatched(EntryLinkFollowed::class, function (EntryLinkFollowed $event) use ($link): bool {
            return $event->entryLink->is($link);
        });
    }

    public function test_returns_404_for_invalid_segment_format(): void
    {
        $this->get('/link/bad_token!')->assertNotFound();
    }

    public function test_returns_404_for_soft_deleted_link(): void
    {
        $link = EntryLink::factory()->create([
            'token' => 'deleted1',
            'target_url' => 'https://example.com/gone',
        ]);
        $link->delete();

        $this->get('/link/'.$link->token)->assertNotFound();
    }
}
