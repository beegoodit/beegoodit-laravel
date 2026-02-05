<?php

namespace BeegoodIT\LaravelFeedback\Livewire;

use BeegoodIT\LaravelFeedback\Models\FeedbackItem;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Livewire\Component;

class FeedbackButton extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function feedbackAction(): Action
    {
        return Action::make('feedback')
            ->label(__('feedback::feedback.menu_item'))
            ->icon('heroicon-o-chat-bubble-left')
            ->iconButton()
            ->tooltip(__('feedback::feedback.button.open'))
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
            ->action(function (array $data, Action $action): void {
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

                    // Close the modal after successful submission
                    $action->close();
                } catch (\Exception $e) {
                    Notification::make()
                        ->title(__('feedback::feedback.submit.error'))
                        ->body(__('feedback::feedback.submit.error_body'))
                        ->danger()
                        ->send();

                    // Keep modal open on error so user can fix and resubmit
                }
            })
            ->after(function (): void {
                // Reset form after successful submission
                $this->form->fill();
            });
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('laravel-feedback::livewire.feedback-button');
    }
}
