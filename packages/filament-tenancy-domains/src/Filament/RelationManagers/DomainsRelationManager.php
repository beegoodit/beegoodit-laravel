<?php

namespace BeegoodIT\FilamentTenancyDomains\Filament\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class DomainsRelationManager extends RelationManager
{
    protected static string $relationship = 'domains';

    protected static ?string $recordTitleAttribute = 'domain';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Forms\Components\TextInput::make('domain')
                    ->required()
                    ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule) => $rule->where('domain', request()->input('domain')))
                    ->maxLength(255),
                Forms\Components\Select::make('type')
                    ->options([
                        'platform' => 'Platform Subdomain',
                        'custom_subdomain' => 'Custom Subdomain',
                        'custom_domain' => 'Custom Domain',
                    ])
                    ->required()
                    ->reactive(),
                Forms\Components\Toggle::make('is_primary')
                    ->label('Primary Domain')
                    ->default(false),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Forms\Components\Section::make('Verification')
                    ->description('DNS verification for custom domains')
                    ->schema([
                        Forms\Components\TextInput::make('verification_token')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Placeholder::make('verified_at')
                            ->content(fn ($record) => $record?->verified_at?->diffForHumans() ?? 'Not verified'),
                    ])
                    ->visible(fn (Forms\Get $get): bool => $get('type') !== 'platform'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('domain')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->colors([
                        'primary' => 'platform',
                        'warning' => 'custom_subdomain',
                        'success' => 'custom_domain',
                    ]),
                Tables\Columns\IconColumn::make('is_primary')
                    ->label('Primary')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                Tables\Columns\IconColumn::make('is_verified')
                    ->label('Verified')
                    ->boolean(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                DeleteBulkAction::make(),
            ]);
    }
}
