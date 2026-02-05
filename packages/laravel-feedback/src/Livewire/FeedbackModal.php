<?php

namespace BeegoodIT\LaravelFeedback\Livewire;

use BeegoodIT\LaravelFeedback\Models\FeedbackItem;
use Livewire\Component;

class FeedbackModal extends Component
{
    public string $subject = '';

    public string $description = '';

    public bool $showSuccess = false;

    public bool $showError = false;

    public ?string $errorMessage = null;

    public function openModal(): void
    {
        if (! auth()->check()) {
            session()->put('intended_feedback_modal', true);
            $this->redirect(route('filament.me.auth.login'));
        }
    }

    public function mount(): void
    {
        // No redirect in mount() - we'll handle auth check when modal opens
    }

    public function submit(): void
    {
        // Reset previous messages
        $this->showSuccess = false;
        $this->showError = false;
        $this->errorMessage = null;

        $this->validate([
            'subject' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
        ]);

        try {
            FeedbackItem::create([
                'subject' => $this->subject,
                'description' => $this->description,
                'created_by' => auth()->id(),
                'user_agent' => request()->userAgent(),
                'ip_address' => request()->ip(),
            ]);

            $this->reset(['subject', 'description']);
            $this->showSuccess = true;

            // Close modal after 1.5 seconds
            $this->js("setTimeout(() => \$flux.modal('feedback').close(), 1500);");
        } catch (\Exception $e) {
            $this->showError = true;
            $this->errorMessage = __('feedback::feedback.submit.error_body');
        }
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('laravel-feedback::livewire.feedback-modal');
    }
}
