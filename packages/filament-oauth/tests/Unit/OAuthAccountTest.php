<?php

use BeeGoodIT\FilamentOAuth\Models\OAuthAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

uses(RefreshDatabase::class);

beforeEach(function () {
    Schema::create('users', function ($table) {
        $table->uuid('id')->primary();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamps();
    });
    
    Schema::create('oauth_accounts', function ($table) {
        $table->uuid('id')->primary();
        $table->uuid('user_id');
        $table->string('provider');
        $table->string('provider_id');
        $table->string('provider_tenant_id')->nullable();
        $table->string('provider_email')->nullable();
        $table->string('avatar_url')->nullable();
        $table->text('access_token')->nullable();
        $table->text('refresh_token')->nullable();
        $table->timestamp('token_expires_at')->nullable();
        $table->timestamps();
    });
});

it('encrypts access and refresh tokens', function () {
    $account = OAuthAccount::create([
        'user_id' => \Illuminate\Support\Str::uuid(),
        'provider' => 'microsoft',
        'provider_id' => '123456',
        'access_token' => 'secret-token',
        'refresh_token' => 'secret-refresh',
    ]);
    
    // Check that tokens are encrypted in database
    $raw = \Illuminate\Support\Facades\DB::table('oauth_accounts')
        ->where('id', $account->id)
        ->first();
    
    expect($raw->access_token)->not->toBe('secret-token');
    expect($account->access_token)->toBe('secret-token'); // But decrypted when accessed
});

it('detects expired tokens', function () {
    $account = OAuthAccount::create([
        'user_id' => \Illuminate\Support\Str::uuid(),
        'provider' => 'microsoft',
        'provider_id' => '123456',
        'token_expires_at' => now()->subHour(),
    ]);
    
    expect($account->isTokenExpired())->toBeTrue();
});

it('detects non-expired tokens', function () {
    $account = OAuthAccount::create([
        'user_id' => \Illuminate\Support\Str::uuid(),
        'provider' => 'microsoft',
        'provider_id' => '123456',
        'token_expires_at' => now()->addHour(),
    ]);
    
    expect($account->isTokenExpired())->toBeFalse();
});

it('scopes by provider', function () {
    $user1Id = \Illuminate\Support\Str::uuid();
    
    OAuthAccount::create([
        'user_id' => $user1Id,
        'provider' => 'microsoft',
        'provider_id' => '123',
    ]);
    
    OAuthAccount::create([
        'user_id' => $user1Id,
        'provider' => 'google',
        'provider_id' => '456',
    ]);
    
    $microsoftAccounts = OAuthAccount::whereProvider('microsoft')->get();
    
    expect($microsoftAccounts)->toHaveCount(1);
    expect($microsoftAccounts->first()->provider)->toBe('microsoft');
});

