<?php

namespace BeeGoodIT\FilamentLegal\Filament\Resources;

use BeeGoodIT\FilamentLegal\Filament\Resources\LegalPolicyResource\Pages;
use BeeGoodIT\FilamentLegal\Models\LegalPolicy;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LegalPolicyResource extends Resource
{
    protected static ?string $model = LegalPolicy::class;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-scale';

    protected static \UnitEnum|string|null $navigationGroup = 'Legal';

    public static function getNavigationGroup(): ?string
    {
        return __('filament-legal::messages.Legal');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-legal::messages.Legal Policies');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make()
                    ->schema([
                        Select::make('type')
                            ->options([
                                'privacy' => __('filament-legal::messages.Privacy Policy'),
                                'terms' => __('filament-legal::messages.Terms of Service'),
                                'imprint' => __('filament-legal::messages.Imprint'),
                                'cookie' => __('filament-legal::messages.Cookie Policy'),
                            ])
                            ->required(),
                        TextInput::make('version')
                            ->placeholder('e.g., 1.0')
                            ->required(),
                        Toggle::make('is_active')
                            ->label(__('filament-legal::messages.Active version'))
                            ->default(false),
                        DateTimePicker::make('published_at')
                            ->default(now()),
                    ])->columns(2),

                Section::make(__('filament-legal::messages.Content'))
                    ->description(__('filament-legal::messages.Provide the content for each supported language.'))
                    ->schema([
                        Tabs::make(__('filament-legal::messages.Languages'))
                            ->tabs([
                                Tabs\Tab::make(__('filament-legal::messages.German'))
                                    ->schema([
                                        RichEditor::make('content.de')
                                            ->label(__('filament-legal::messages.Content (DE)'))
                                            ->required(),
                                    ]),
                                Tabs\Tab::make(__('filament-legal::messages.English'))
                                    ->schema([
                                        RichEditor::make('content.en')
                                            ->label(__('filament-legal::messages.Content (EN)'))
                                            ->required(),
                                    ]),
                                Tabs\Tab::make(__('filament-legal::messages.Spanish'))
                                    ->schema([
                                        RichEditor::make('content.es')
                                            ->label(__('filament-legal::messages.Content (ES)'))
                                            ->required(),
                                    ]),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('type')
                    ->formatStateUsing(fn($state) => ucfirst($state))
                    ->sortable(),
                TextColumn::make('version')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('filament-legal::messages.Active'))
                    ->boolean(),
                TextColumn::make('published_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'privacy' => __('filament-legal::messages.Privacy Policy'),
                        'terms' => __('filament-legal::messages.Terms of Service'),
                        'imprint' => __('filament-legal::messages.Imprint'),
                        'cookie' => __('filament-legal::messages.Cookie Policy'),
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            \BeeGoodIT\FilamentLegal\Filament\RelationManagers\PolicyAcceptancesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLegalPolicies::route('/'),
            'create' => Pages\CreateLegalPolicy::route('/create'),
            'edit' => Pages\EditLegalPolicy::route('/{record}/edit'),
        ];
    }
}
