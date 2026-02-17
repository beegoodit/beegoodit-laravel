<?php

namespace BeegoodIT\FilamentLegal\Filament\Resources;

use BeegoodIT\FilamentLegal\Filament\Resources\LegalPolicyResource\Pages;
use BeegoodIT\FilamentLegal\FilamentLegalPlugin;
use BeegoodIT\FilamentLegal\Models\LegalPolicy;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LegalPolicyResource extends Resource
{
    protected static ?string $model = LegalPolicy::class;

    protected static ?string $tenantOwnershipRelationshipName = 'owner';

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

    public static function getBreadcrumb(): string
    {
        return __('filament-legal::messages.Legal Policies');
    }

    public static function getLabel(): ?string
    {
        return __('filament-legal::messages.Legal Policy');
    }

    public static function getPluralLabel(): ?string
    {
        return __('filament-legal::messages.Legal Policies');
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Select::make('type')
                    ->label(__('filament-legal::messages.Policy Type'))
                    ->options([
                        'privacy' => __('filament-legal::messages.Privacy Policy'),
                        'terms' => __('filament-legal::messages.Terms of Service'),
                        'imprint' => __('filament-legal::messages.Imprint'),
                        'cookie' => __('filament-legal::messages.Cookie Policy'),
                    ])
                    ->required(),
                TextInput::make('version')
                    ->label(__('filament-legal::messages.Version'))
                    ->placeholder('e.g., 1.0')
                    ->required(),
                Toggle::make('is_active')
                    ->label(__('filament-legal::messages.Active version'))
                    ->default(false),
                DateTimePicker::make('published_at')
                    ->label(__('filament-legal::messages.Published At'))
                    ->default(now()),
                MorphToSelect::make('owner')
                    ->label(__('filament-legal::messages.Owner'))
                    ->types(collect(FilamentLegalPlugin::get()->getLegalableModels())
                        ->map(fn ($model) => MorphToSelect\Type::make($model)->titleAttribute('name'))
                        ->toArray())
                    ->columnSpanFull()
                    ->hidden(fn () => filament()->hasTenancy()),

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
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading(__('filament-legal::messages.No legal policies found'))
            ->columns([
                TextColumn::make('type')
                    ->label(__('filament-legal::messages.Policy Type'))
                    ->formatStateUsing(fn ($state): string => match ($state) {
                        'privacy' => __('filament-legal::messages.Privacy Policy'),
                        'terms' => __('filament-legal::messages.Terms of Service'),
                        'imprint' => __('filament-legal::messages.Imprint'),
                        'cookie' => __('filament-legal::messages.Cookie Policy'),
                        default => ucfirst((string) $state),
                    })
                    ->sortable(),
                TextColumn::make('version')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('filament-legal::messages.Active'))
                    ->boolean(),
                TextColumn::make('published_at')
                    ->label(__('filament-legal::messages.Published At'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('owner_type')
                    ->label(__('filament-legal::messages.Owner Type'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('owner_id')
                    ->label(__('filament-legal::messages.Owner ID'))
                    ->toggleable(isToggledHiddenByDefault: true),
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
                SelectFilter::make('owner_type')
                    ->label(__('filament-legal::messages.Owner Type'))
                    ->options(collect(FilamentLegalPlugin::get()->getLegalableModels())
                        ->mapWithKeys(fn ($model): array => [$model => __('filament-legal::messages.'.class_basename($model))])
                        ->all()),
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
            \BeegoodIT\FilamentLegal\Filament\RelationManagers\PolicyAcceptancesRelationManager::class,
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
