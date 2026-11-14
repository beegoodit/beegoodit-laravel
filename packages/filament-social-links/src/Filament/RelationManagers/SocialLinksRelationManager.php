<?php

namespace BeegoodIT\FilamentSocialLinks\Filament\RelationManagers;

use BeegoodIT\FilamentSocialLinks\Filament\Resources\SocialLinkResource;
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
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('handle')
            ->columns(SocialLinkResource::linkColumns())
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['sort_order'] = (int) $this->getOwnerRecord()->socialLinks()->max('sort_order') + 1;

                        return $data;
                    }),
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
