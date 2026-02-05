<div>
    @if(auth()->check())
        <flux:modal.trigger name="feedback">
            <flux:button variant="subtle" square aria-label="{{ __('feedback::feedback.button.open') }}" title="{{ __('feedback::feedback.button.open') }}">
                <flux:icon.chat-bubble-left variant="outline" class="size-5" />
            </flux:button>
        </flux:modal.trigger>
    @else
        <flux:button 
            variant="subtle" 
            square 
            aria-label="{{ __('feedback::feedback.button.open') }}" 
            title="{{ __('feedback::feedback.button.open') }}"
            wire:click="openModal"
        >
            <flux:icon.chat-bubble-left variant="outline" class="size-5" />
        </flux:button>
    @endif

    <flux:modal name="feedback" class="md:w-96">
        <form wire:submit="submit" class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('feedback::feedback.modal.title') }}</flux:heading>
                <flux:text class="mt-2">{{ __('feedback::feedback.modal.description') }}</flux:text>
            </div>

            {{-- Success Message --}}
            <div x-show="$wire.showSuccess" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <flux:callout color="green" icon="check-circle">
                    <flux:callout.text>{{ __('feedback::feedback.submit.success') }}</flux:callout.text>
                </flux:callout>
            </div>

            {{-- Error Message --}}
            <div x-show="$wire.showError" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
                <flux:callout color="red" icon="exclamation-circle">
                    <flux:callout.heading>{{ __('feedback::feedback.submit.error') }}</flux:callout.heading>
                    <flux:callout.text x-text="$wire.errorMessage"></flux:callout.text>
                </flux:callout>
            </div>

            <div x-show="!$wire.showSuccess" x-transition>
                <flux:input
                    wire:model="subject"
                    label="{{ __('feedback::feedback.form.subject') }}"
                    placeholder="{{ __('feedback::feedback.form.subject_placeholder') }}"
                    required
                />

                <flux:textarea
                    wire:model="description"
                    label="{{ __('feedback::feedback.form.description') }}"
                    placeholder="{{ __('feedback::feedback.form.description_placeholder') }}"
                    required
                    rows="5"
                />
            </div>

            <div class="flex gap-2">
                <flux:spacer />
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('feedback::feedback.form.cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" x-show="!$wire.showSuccess">
                    {{ __('feedback::feedback.form.submit') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
