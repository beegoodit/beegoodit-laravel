<?php

namespace BeegoodIT\LaravelFeedback\Filament\Resources;

use BackedEnum;
use BeegoodIT\LaravelFeedback\Filament\Resources\FeedbackItemResource\Pages\CreateFeedbackItem;
use BeegoodIT\LaravelFeedback\Filament\Resources\FeedbackItemResource\Pages\EditFeedbackItem;
use BeegoodIT\LaravelFeedback\Filament\Resources\FeedbackItemResource\Pages\ListFeedbackItems;
use BeegoodIT\LaravelFeedback\Filament\Resources\FeedbackItemResource\Pages\ViewFeedbackItem;
use BeegoodIT\LaravelFeedback\Filament\Resources\FeedbackItemResource\Schemas\FeedbackItemInfolist;
use BeegoodIT\LaravelFeedback\Models\FeedbackItem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class FeedbackItemResource extends Resource
{
    protected static ?string $model = FeedbackItem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeft;

    protected static ?int $navigationSort = 10;

    protected static UnitEnum|string|null $navigationGroup = 'Feedback';

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getModel()::count();
    }

    public static function getNavigationLabel(): string
    {
        return __('feedback::feedback.model.plural');
    }

    public static function getModelLabel(): string
    {
        return __('feedback::feedback.model.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('feedback::feedback.model.plural');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                TextInput::make('subject')
                        ->label(__('feedback::feedback.form.subject'))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('description')
                        ->label(__('feedback::feedback.form.description'))
                    ->required()
                    ->rows(5)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')
                    ->label(__('feedback::feedback.table.subject'))
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('description')
                    ->label(__('feedback::feedback.table.description'))
                    ->limit(100)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('creator.name')
                    ->label(__('feedback::feedback.table.creator'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label(__('feedback::feedback.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('ip_address')
                    ->label(__('feedback::feedback.table.ip_address'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->copyable(),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('created_from')
                            ->label(__('feedback::feedback.filters.created_from')),
                        \Filament\Forms\Components\DatePicker::make('created_until')
                            ->label(__('feedback::feedback.filters.created_until')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
                Filter::make('ip_address')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('ip_address')
                            ->label(__('feedback::feedback.filters.ip_address')),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['ip_address'],
                                fn (Builder $query, $ip): Builder => $query->where('ip_address', 'like', "%{$ip}%")
                            );
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Schema $schema): Schema
    {
        return FeedbackItemInfolist::configure($schema);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListFeedbackItems::route('/'),
            'create' => CreateFeedbackItem::route('/create'),
            'view' => ViewFeedbackItem::route('/{record}'),
            'edit' => EditFeedbackItem::route('/{record}/edit'),
        ];
    }
}
