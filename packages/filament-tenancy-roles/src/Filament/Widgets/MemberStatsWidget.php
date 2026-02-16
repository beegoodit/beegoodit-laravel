<?php

namespace BeegoodIT\FilamentTenancyRoles\Filament\Widgets;

use BeegoodIT\FilamentTenancyRoles\Enums\TeamRole;
use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;

class MemberStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        /** @var Model|null $tenant */
        $tenant = Filament::getTenant();

        if (! method_exists($tenant, 'members')) {
            return [];
        }

        $members = $tenant->members()->get();

        $ownerCount = $members->filter(fn ($m): bool => $m->membership?->role === TeamRole::Owner)->count();
        $adminCount = $members->filter(fn ($m): bool => $m->membership?->role === TeamRole::Admin)->count();
        $memberCount = $members->filter(fn ($m): bool => $m->membership?->role === TeamRole::Member)->count();
        $totalCount = $members->count();

        return [
            Stat::make(__('filament-tenancy-roles::messages.Total Members'), (string) $totalCount)
                ->description(__('filament-tenancy-roles::messages.Owners / Admins / Members'))
                ->value("{$ownerCount} / {$adminCount} / {$memberCount}")
                ->descriptionIcon('heroicon-m-user-group')
                ->color('primary'),
        ];
    }
}
