<?php

namespace BeegoodIT\FilamentOpeningTimes\Policies;

use BeegoodIT\FilamentOpeningTimes\Models\Schedule;
use Illuminate\Database\Eloquent\Model;

class SchedulePolicy
{
    public function viewAny($user): bool
    {
        return true;
    }

    public function view($user, Schedule $schedule): bool
    {
        return $this->delegatesToOpenable($user, $schedule, 'view');
    }

    public function create($user): bool
    {
        return true;
    }

    public function update($user, Schedule $schedule): bool
    {
        return $this->delegatesToOpenable($user, $schedule, 'update');
    }

    public function delete($user, Schedule $schedule): bool
    {
        return $this->delegatesToOpenable($user, $schedule, 'delete');
    }

    public function restore($user, Schedule $schedule): bool
    {
        return $this->delegatesToOpenable($user, $schedule, 'update');
    }

    public function forceDelete($user, Schedule $schedule): bool
    {
        return $this->delegatesToOpenable($user, $schedule, 'forceDelete');
    }

    private function delegatesToOpenable($user, Schedule $schedule, string $ability): bool
    {
        $openable = $schedule->openable;
        if (! $openable instanceof Model) {
            return false;
        }

        return $user->can($ability, $openable);
    }
}
