<?php

namespace BeegoodIT\LaravelFeedback\Filament\Pages;

use BeegoodIT\LaravelFeedback\Models\FeedbackItem;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class FeedbackPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left';

    protected static string $view = 'laravel-feedback::filament.pages.feedback';

    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
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
            ])
            ->statePath('data');
    }

    public function submit(): void
    {
        $data = $this->form->getState();

        FeedbackItem::create([
            'subject' => $data['subject'],
            'description' => $data['description'],
            'created_by' => auth()->id(),
            'user_agent' => request()->userAgent(),
            'ip_address' => request()->ip(),
        ]);

        $this->form->fill();

        Notification::make()
            ->title(__('feedback::feedback.submit.success'))
            ->success()
            ->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('submit')
                ->label(__('feedback::feedback.form.submit'))
                ->submit('submit'),
        ];
    }

    public static function getSlug(): string
    {
        return 'feedback';
    }
}
