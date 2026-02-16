<?php

namespace BeegoodIT\FilamentTenancy\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProfileCompletenessWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        /** @var Model|null $tenant */
        $tenant = Filament::getTenant();

        if (! $tenant) {
            return [];
        }

        return [
            $this->getLogoStat($tenant),
            $this->getBrandingStat($tenant),
            $this->getSlugStat($tenant),
            ...$this->getOptionalDomainStat($tenant),
        ];
    }

    private function getLogoStat(Model $tenant): Stat
    {
        $hasLogo = ! empty($tenant->logo);

        return Stat::make(__('filament-tenancy::messages.Team Logo'), $hasLogo ? __('filament-tenancy::messages.Uploaded') : __('filament-tenancy::messages.Missing'))
            ->description($hasLogo ? __('filament-tenancy::messages.Logo looks great!') : __('filament-tenancy::messages.Adding a logo improves brand recognition.'))
            ->descriptionIcon($hasLogo ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
            ->color($hasLogo ? 'success' : 'warning');
    }

    private function getBrandingStat(Model $tenant): Stat
    {
        $hasColors = ! empty($tenant->primary_color);

        return Stat::make(__('filament-tenancy::messages.Brand Colors'), $hasColors ? __('filament-tenancy::messages.Defined') : __('filament-tenancy::messages.Default'))
            ->description($hasColors ? __('filament-tenancy::messages.Your brand identity is set.') : __('filament-tenancy::messages.Set brand colors to customize the team portal.'))
            ->descriptionIcon($hasColors ? 'heroicon-m-swatch' : 'heroicon-m-paint-brush')
            ->color($hasColors ? 'success' : 'warning');
    }

    private function getSlugStat(Model $tenant): Stat
    {
        $nameSlug = Str::slug($tenant->name ?? '');
        $isSlugCustomized = ($tenant->slug ?? '') !== $nameSlug;

        return Stat::make(__('filament-tenancy::messages.Custom URL'), $isSlugCustomized ? __('filament-tenancy::messages.Customized') : __('filament-tenancy::messages.Standard'))
            ->description($tenant->slug ?? '')
            ->descriptionIcon('heroicon-m-link')
            ->color('success');
    }

    /**
     * @return array<int, Stat>
     */
    private function getOptionalDomainStat(Model $tenant): array
    {
        if (! method_exists($tenant, 'domains')) {
            return [];
        }

        $hasDomain = $tenant->domains()->exists();

        return [
            Stat::make(__('filament-tenancy::messages.Web Presence'), $hasDomain ? __('filament-tenancy::messages.Active') : __('filament-tenancy::messages.Inactive'))
                ->description($hasDomain ? __('filament-tenancy::messages.Custom domain is configured.') : __('filament-tenancy::messages.Connect a custom domain for a professional look.'))
                ->descriptionIcon('heroicon-m-globe-alt')
                ->color($hasDomain ? 'success' : 'gray'),
        ];
    }
}
