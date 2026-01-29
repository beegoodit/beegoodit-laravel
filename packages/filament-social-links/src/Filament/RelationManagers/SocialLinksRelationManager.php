<?php

namespace BeegoodIT\FilamentSocialLinks\Filament\RelationManagers;

use BeegoodIT\FilamentSocialLinks\Models\SocialPlatform;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SocialLinksRelationManager extends RelationManager
{
    protected static string $relationship = 'socialLinks';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('social_platform_id')
                ->label(__('filament-social-links::social.platform'))
                ->options(fn () => SocialPlatform::query()
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->pluck('name', 'id')
                )
                ->required()
                ->searchable()
                ->preload(),

            TextInput::make('handle')
                ->label(__('filament-social-links::social.handle'))
                ->placeholder(__('filament-social-links::social.handle_placeholder'))
                ->required()
                ->maxLength(255),

            TextInput::make('sort_order')
                ->label(__('filament-social-links::social.sort_order'))
                ->numeric()
                ->default(0),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('handle')
            ->columns([
                TextColumn::make('platform.name')
                    ->label(__('filament-social-links::social.platform'))
                    ->sortable(),

                TextColumn::make('handle')
                    ->label(__('filament-social-links::social.handle'))
                    ->searchable(),

                TextColumn::make('url')
                    ->label('URL')
                    ->limit(40)
                    ->copyable(),

                TextColumn::make('sort_order')
                    ->label(__('filament-social-links::social.sort_order'))
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                CreateAction::make(),
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

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('filament-social-links::social.platforms');
    }
}
