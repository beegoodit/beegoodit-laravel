<?php

namespace BeegoodIT\FilamentI18n\Tests\Feature;

use BeegoodIT\FilamentI18n\Middleware\SetLocale;
use BeegoodIT\FilamentI18n\Models\Concerns\HasI18nPreferences;
use BeegoodIT\FilamentI18n\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;

class TestUser extends Authenticatable
{
    use HasI18nPreferences;

    protected $table = 'users';

    protected $guarded = [];
}

class SetLocaleMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Model::unguard();
    }

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('auth.providers.users.model', TestUser::class);
    }

    public function test_it_sets_locale_from_authenticated_user(): void
    {
        // Create a mock user object with locale
        $user = new TestUser;
        $user->locale = 'de';

        // Mock the getLocale method if it exists
        if (method_exists($user, 'getLocale')) {
            // The trait will handle this
        }

        $request = Request::create('/');
        $request->setUserResolver(fn (): \BeegoodIT\FilamentI18n\Tests\Feature\TestUser => $user);

        $middleware = new SetLocale;
        $middleware->handle($request, fn ($req) => $req);

        $this->assertEquals('de', app()->getLocale());
    }

    public function test_it_does_not_change_locale_when_user_not_authenticated(): void
    {
        $originalLocale = app()->getLocale();

        $request = Request::create('/');
        $middleware = new SetLocale;
        $middleware->handle($request, fn ($req) => $req);

        $this->assertEquals($originalLocale, app()->getLocale());
    }
}
