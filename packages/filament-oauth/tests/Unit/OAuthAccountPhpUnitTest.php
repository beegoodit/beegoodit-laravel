<?php

namespace BeeGoodIT\FilamentOAuth\Tests\Unit;

use BeeGoodIT\FilamentOAuth\Models\OAuthAccount;
use BeeGoodIT\FilamentOAuth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OAuthAccountPhpUnitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadLaravelMigrations();

        Schema::create('oauth_accounts', function ($table): void {
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
    }

    public function test_it_encrypts_access_and_refresh_tokens(): void
    {
        $account = OAuthAccount::create([
            'user_id' => \Illuminate\Support\Str::uuid(),
            'provider' => 'microsoft',
            'provider_id' => '123456',
            'access_token' => 'secret-token',
            'refresh_token' => 'secret-refresh',
        ]);

        // Check that tokens are encrypted in database
        $raw = DB::table('oauth_accounts')->where('id', $account->id)->first();

        $this->assertNotEquals('secret-token', $raw->access_token);
        $this->assertEquals('secret-token', $account->access_token);
    }

    public function test_it_detects_expired_tokens(): void
    {
        $account = OAuthAccount::create([
            'user_id' => \Illuminate\Support\Str::uuid(),
            'provider' => 'microsoft',
            'provider_id' => '123456',
            'token_expires_at' => now()->subHour(),
        ]);

        $this->assertTrue($account->isTokenExpired());
    }

    public function test_it_detects_non_expired_tokens(): void
    {
        $account = OAuthAccount::create([
            'user_id' => \Illuminate\Support\Str::uuid(),
            'provider' => 'microsoft',
            'provider_id' => '123456',
            'token_expires_at' => now()->addHour(),
        ]);

        $this->assertFalse($account->isTokenExpired());
    }

    public function test_it_scopes_by_provider(): void
    {
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

        $this->assertCount(1, $microsoftAccounts);
        $this->assertEquals('microsoft', $microsoftAccounts->first()->provider);
    }
}
