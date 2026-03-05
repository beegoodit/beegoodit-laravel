<?php

namespace BeegoodIT\FilamentSocialGraph\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        $maxFiles = config('filament-social-graph.attachments.max_files', 5);
        $maxKb = config('filament-social-graph.attachments.max_file_size_kb', 5120);
        $mimes = config('filament-social-graph.attachments.allowed_mimes', ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf']);

        return [
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string', 'max:65535'],
            'attachments' => ['nullable', 'array', 'max:'.$maxFiles],
            'attachments.*' => ['required', 'file', 'max:'.$maxKb, 'mimes:'.implode(',', $mimes)],
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
