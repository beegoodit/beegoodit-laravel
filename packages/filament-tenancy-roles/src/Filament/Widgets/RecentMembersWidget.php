<?php

namespace BeegoodIT\FilamentTenancyRoles\Filament\Widgets;

use Filament\Facades\Filament;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class RecentMembersWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        /** @var Model|null $tenant */
        $tenant = Filament::getTenant();

        if (! method_exists($tenant, 'members')) {
            // Return an empty Eloquent query if the relationship doesn't exist
            // We use the tenant class itself to get a valid Eloquent Builder
            $modelClass = Filament::getTenantModel();

            return $table->query($modelClass::query()->whereRaw('1=0'));
        }

        return $table
            ->query(
                $tenant->members()
                    ->getQuery()
                    ->select('users.*')
                    ->addSelect([
                        'team_user.role as role',
                        'team_user.created_at as joined_at',
                    ])
                    ->latest('team_user.created_at')
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('name')
                    ->label(__('filament-tenancy-roles::messages.Name'))
                    ->weight('bold'),
                TextColumn::make('email')
                    ->label(__('filament-tenancy-roles::messages.Email')),
                TextColumn::make('role')
                    ->label(__('filament-tenancy-roles::messages.Role'))
                    ->badge()
                    ->formatStateUsing(fn ($state): string => __("filament-tenancy-roles::messages.roles.{$state}"))
                    ->color(fn ($state): string => match ($state) {
                        'owner', 'admin' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('joined_at')
                    ->label(__('filament-tenancy-roles::messages.Joined'))
                    ->dateTime()
                    ->since()
                    ->description(fn ($record): ?string => $record->joined_at ? \Illuminate\Support\Carbon::parse($record->joined_at)->toFormattedDateString() : null),
            ])
            ->paginated(false)
            ->headerActions([])
            ->actions([])
            ->emptyStateHeading(__('filament-tenancy-roles::messages.No members yet'))
            ->emptyStateDescription(__('filament-tenancy-roles::messages.Invite users to start collaborating.'));
    }

    public function getHeading(): string
    {
        return __('filament-tenancy-roles::messages.Recent Members');
    }
}
