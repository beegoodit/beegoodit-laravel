<?php

namespace BeegoodIT\FilamentOpeningTimes\Tests\Unit;

use BeegoodIT\FilamentOpeningTimes\Models\Schedule;
use BeegoodIT\FilamentOpeningTimes\Policies\SchedulePolicy;
use BeegoodIT\FilamentOpeningTimes\Tests\Fixtures\TestOpenable;
use BeegoodIT\FilamentOpeningTimes\Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Gate;

class SchedulePolicyTest extends TestCase
{
    public function test_view_any_is_allowed(): void
    {
        $policy = new SchedulePolicy;
        $user = new User;

        $this->assertTrue($policy->viewAny($user));
    }

    public function test_view_delegates_to_openable_when_authorized(): void
    {
        Gate::before(function ($user, string $ability, array $arguments): ?bool {
            $model = $arguments[0] ?? null;
            if ($model instanceof TestOpenable && $ability === 'view') {
                return true;
            }

            return null;
        });

        $openable = TestOpenable::query()->create();
        $schedule = Schedule::query()->create([
            'openable_type' => $openable->getMorphClass(),
            'openable_id' => $openable->getKey(),
            'timezone' => 'UTC',
            'active_from' => Carbon::now()->subDay(),
            'active_to' => Carbon::now()->addDay(),
        ]);

        $policy = new SchedulePolicy;
        $user = new User;

        $this->assertTrue($policy->view($user, $schedule));
    }

    public function test_view_denied_when_user_cannot_view_openable(): void
    {
        Gate::before(function ($user, string $ability, array $arguments): ?bool {
            $model = $arguments[0] ?? null;
            if ($model instanceof TestOpenable) {
                return false;
            }

            return null;
        });

        $openable = TestOpenable::query()->create();
        $schedule = Schedule::query()->create([
            'openable_type' => $openable->getMorphClass(),
            'openable_id' => $openable->getKey(),
            'timezone' => 'UTC',
            'active_from' => Carbon::now()->subDay(),
            'active_to' => Carbon::now()->addDay(),
        ]);

        $policy = new SchedulePolicy;
        $user = new User;

        $this->assertFalse($policy->view($user, $schedule));
    }

    public function test_view_denied_when_openable_missing(): void
    {
        $schedule = Schedule::query()->create([
            'openable_type' => TestOpenable::class,
            'openable_id' => 'ffffffff-ffff-ffff-ffff-ffffffffffff',
            'timezone' => 'UTC',
            'active_from' => Carbon::now()->subDay(),
            'active_to' => Carbon::now()->addDay(),
        ]);

        $policy = new SchedulePolicy;
        $user = new User;

        $this->assertFalse($policy->view($user, $schedule));
    }

    public function test_update_delegates_like_view(): void
    {
        Gate::before(function ($user, string $ability, array $arguments): ?bool {
            $model = $arguments[0] ?? null;
            if ($model instanceof TestOpenable && $ability === 'update') {
                return true;
            }

            return null;
        });

        $openable = TestOpenable::query()->create();
        $schedule = Schedule::query()->create([
            'openable_type' => $openable->getMorphClass(),
            'openable_id' => $openable->getKey(),
            'timezone' => 'UTC',
            'active_from' => Carbon::now()->subDay(),
            'active_to' => Carbon::now()->addDay(),
        ]);

        $policy = new SchedulePolicy;
        $user = new User;

        $this->assertTrue($policy->update($user, $schedule));
    }
}
