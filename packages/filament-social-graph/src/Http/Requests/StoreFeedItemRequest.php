<?php

namespace BeegoodIT\FilamentSocialGraph\Http\Requests;

use BeegoodIT\FilamentSocialGraph\Enums\Visibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreFeedItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:65535'],
            'visibility' => ['required', 'string', Rule::enum(Visibility::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'body.required' => __('filament-social-graph::feed_item.body_required'),
        ];
    }
}
