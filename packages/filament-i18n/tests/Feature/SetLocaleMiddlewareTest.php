<?php

use BeeGoodIT\FilamentI18n\Middleware\SetLocale;
use BeeGoodIT\FilamentI18n\Models\Concerns\HasI18nPreferences;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

it('sets locale from authenticated user', function () {
    $user = TestUser::create([
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => bcrypt('password'),
        'locale' => 'de',
    ]);
    
    $request = Request::create('/');
    $request->setUserResolver(fn () => $user);
    
    $middleware = new SetLocale();
    $middleware->handle($request, fn ($req) => $req);
    
    expect(app()->getLocale())->toBe('de');
});

it('does not change locale when user not authenticated', function () {
    $originalLocale = app()->getLocale();
    
    $request = Request::create('/');
    $middleware = new SetLocale();
    $middleware->handle($request, fn ($req) => $req);
    
    expect(app()->getLocale())->toBe($originalLocale);
});

class TestUser extends Authenticatable
{
    use HasI18nPreferences;
    
    protected $table = 'users';
    protected $guarded = [];
}

