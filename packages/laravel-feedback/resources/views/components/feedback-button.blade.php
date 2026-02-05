@php
    use BeegoodIT\LaravelFeedback\Filament\Pages\FeedbackPage;
    use Filament\Actions\Action;
    use Filament\Forms\Components\Textarea;
    use Filament\Forms\Components\TextInput;
    use Filament\Notifications\Notification;
    use BeegoodIT\LaravelFeedback\Models\FeedbackItem;
@endphp

@php
    $action = Action::make('feedback')
        ->label(__('feedback::feedback.menu_item'))
        ->icon('heroicon-o-chat-bubble-left')
        ->modalHeading(__('feedback::feedback.modal.title'))
        ->modalDescription(__('feedback::feedback.modal.description'))
        ->modalSubmitActionLabel(__('feedback::feedback.form.submit'))
        ->modalCancelActionLabel(__('feedback::feedback.form.cancel'))
        ->form([
            TextInput::make('subject')
                ->label(__('feedback::feedback.form.subject'))
                ->placeholder(__('feedback::feedback.form.subject_placeholder'))
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->label(__('feedback::feedback.form.description'))
                ->placeholder(__('feedback::feedback.form.description_placeholder'))
                ->required()
                ->rows(5),
        ])
        ->action(function (array $data): void {
            try {
                FeedbackItem::create([
                    'subject' => $data['subject'],
                    'description' => $data['description'],
                    'created_by' => auth()->id(),
                    'user_agent' => request()->userAgent(),
                    'ip_address' => request()->ip(),
                ]);

                Notification::make()
                    ->title(__('feedback::feedback.submit.success'))
                    ->success()
                    ->send();
            } catch (\Exception $e) {
                Notification::make()
                    ->title(__('feedback::feedback.submit.error'))
                    ->body(__('feedback::feedback.submit.error_body'))
                    ->danger()
                    ->send();
            }
        });
@endphp

<div class="flex items-center">
    {{ $action }}
</div>
