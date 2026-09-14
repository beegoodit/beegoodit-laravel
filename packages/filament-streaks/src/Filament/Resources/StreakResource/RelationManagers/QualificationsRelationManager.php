<?php

namespace BeegoodIT\FilamentStreaks\Filament\Resources\StreakResource\RelationManagers;

use BeegoodIT\FilamentStreaks\Filament\Concerns\ValidatesQualificationActivityWindow;
use BeegoodIT\FilamentStreaks\Filament\Resources\QualificationResource;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QualificationsRelationManager extends RelationManager
{
    use ValidatesQualificationActivityWindow;

    protected static string $relationship = 'qualifications';

    public static function getTitle(\Illuminate\Database\Eloquent\Model $ownerRecord, string $pageClass): string
    {
        return __('filament-streaks::qualification.plural');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                ...QualificationResource::eventFormComponents(),
                DateTimePicker::make('happened_at')
                    ->label(__('filament-streaks::qualification.fields.happened_at'))
                    ->required()
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('event_type')
                    ->label(__('filament-streaks::qualification.fields.event'))
                    ->formatStateUsing(fn (string $state): string => class_basename($state))
                    ->toggleable(),
                TextColumn::make('happened_at')
                    ->label(__('filament-streaks::qualification.fields.happened_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['streak_id'] = $this->getOwnerRecord()->getKey();
                        $this->assertWithinActivityWindow($data);

                        return $data;
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['streak_id'] = $this->getOwnerRecord()->getKey();
                        $this->assertWithinActivityWindow($data);

                        return $data;
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('happened_at', 'desc');
    }
}
