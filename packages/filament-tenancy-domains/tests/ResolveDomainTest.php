<?php

namespace BeegoodIT\FilamentTenancyDomains\Tests;

use BeegoodIT\FilamentTenancyDomains\Http\Middleware\ResolveDomainMiddleware;
use Illuminate\Http\Request;

class ResolveDomainTest extends TestCase
{
    public function test_it_resolves_a_domain_to_an_entity(): void
    {
        $tour = TestTour::create([
            'name' => 'Test Tour',
            'slug' => 'test-tour',
        ]);

        $tour->domains()->create([
            'domain' => 'tour.foosbeaver.app',
            'type' => 'platform',
            'is_active' => true,
        ]);

        $request = Request::create('http://tour.foosbeaver.app');
        $middleware = new ResolveDomainMiddleware;

        $middleware->handle($request, function ($req) use ($tour) {
            $this->assertTrue(app()->bound('resolvedDomain'));
            $this->assertTrue(app()->bound('resolvedEntity'));
            $this->assertEquals($tour->id, resolve('resolvedEntity')->id);

            return response('ok');
        });
    }

    public function test_it_does_not_resolve_inactive_domains(): void
    {
        $tour = TestTour::create([
            'name' => 'Test Tour',
            'slug' => 'test-tour',
        ]);

        $tour->domains()->create([
            'domain' => 'tour.foosbeaver.app',
            'type' => 'platform',
            'is_active' => false,
        ]);

        $request = Request::create('http://tour.foosbeaver.app');
        $middleware = new ResolveDomainMiddleware;

        $middleware->handle($request, function ($req) {
            $this->assertFalse(app()->bound('resolvedDomain'));
            $this->assertFalse(app()->bound('resolvedEntity'));

            return response('ok');
        });
    }
}
