<?php

namespace BeeGoodIT\FilamentLegal\Tests;

use BeeGoodIT\FilamentLegal\Http\Middleware\EnsureLegalAcceptance;
use BeeGoodIT\FilamentLegal\Models\LegalPolicy;
use Illuminate\Support\Facades\Route;

class LegalGateTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware(['web', EnsureLegalAcceptance::class])->get('/dashboard', fn() => 'dashboard')->name('dashboard');
    }

    /** @test */
    public function test_users_are_redirected_to_legal_acceptance_page_if_not_accepted(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        LegalPolicy::create([
            'type' => 'privacy',
            'version' => '1.0',
            'content' => ['en' => 'Test Privacy Policy'],
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect(route('filament-legal.acceptance'));
    }

    /** @test */
    public function test_users_can_access_dashboard_after_accepting_policy(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);

        $policy = LegalPolicy::create([
            'type' => 'privacy',
            'version' => '1.0',
            'content' => ['en' => 'Test Privacy Policy'],
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('filament-legal.submit-acceptance'))
            ->assertRedirect();

        $this->get('/dashboard')
            ->assertOk()
            ->assertSee('dashboard');

        $this->assertDatabaseHas('policy_acceptances', [
            'user_id' => $user->id,
            'legal_policy_id' => $policy->id,
        ]);
    }
}
