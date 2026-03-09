<?php

namespace BeegoodIT\FilamentLegal\Tests;

use BeegoodIT\FilamentLegal\Http\Controllers\LegalController;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\View as ViewFacade;

class LegalControllerTest extends TestCase
{
    private LegalController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        ViewFacade::addLocation(__DIR__.'/fixtures/views');
        $this->controller = new LegalController;
    }

    /** @test */
    public function test_returns_default_imprint_view_when_resolved_entity_not_bound(): void
    {
        $view = $this->controller->imprint();

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame('imprint', $view->name());
    }

    /** @test */
    public function test_returns_default_privacy_policy_view_when_resolved_entity_not_bound(): void
    {
        $view = $this->controller->privacyPolicy();

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame('privacy-policy', $view->name());
    }

    /** @test */
    public function test_returns_default_cookie_policy_view_when_resolved_entity_not_bound(): void
    {
        $view = $this->controller->cookiePolicy();

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame('cookie-policy', $view->name());
    }

    /** @test */
    public function test_returns_branded_imprint_view_when_resolved_entity_bound(): void
    {
        $owner = UserWithLegal::create([
            'name' => 'Test Owner',
            'email' => 'owner@example.com',
            'password' => 'password',
        ]);

        $this->app->instance('resolvedEntity', $owner);

        $view = $this->controller->imprint();

        $this->assertInstanceOf(View::class, $view);
        $this->assertSame('branded.imprint', $view->name());
        $data = $view->getData();
        $this->assertArrayHasKey('team', $data);
        $this->assertSame($owner, $data['team']);
        $this->assertArrayHasKey('identity', $data);
        $this->assertArrayHasKey('policy', $data);
    }
}
