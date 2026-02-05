<?php

namespace BeegoodIT\LaravelFeedback\Filament\Resources\FeedbackItemResource\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FeedbackItemInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('feedback::feedback.form.subject'))
                    ->schema([
                        TextEntry::make('subject')
                            ->label(__('feedback::feedback.form.subject')),
                        TextEntry::make('description')
                            ->label(__('feedback::feedback.form.description'))
                            ->columnSpanFull(),
                        TextEntry::make('creator.name')
                            ->label(__('feedback::feedback.table.creator')),
                        TextEntry::make('created_at')
                            ->label(__('feedback::feedback.table.created_at'))
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('feedback::feedback.infolist.metadata'))
                    ->schema([
                        TextEntry::make('ip_address')
                            ->label(__('feedback::feedback.infolist.ip_address'))
                            ->copyable(),
                        TextEntry::make('user_agent')
                            ->label(__('feedback::feedback.infolist.user_agent'))
                            ->copyable()
                            ->wrap()
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),
            ]);
    }
}
