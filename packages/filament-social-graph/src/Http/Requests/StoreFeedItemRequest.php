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
        $maxFileSize = config('filament-social-graph.attachments.max_file_size', 10240);
        $allowedMimes = config('filament-social-graph.attachments.allowed_mime_types', [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
        ]);

        return [
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:65535'],
            'visibility' => ['required', 'string', Rule::enum(Visibility::class)],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:'.$maxFileSize, 'mimetypes:'.implode(',', $allowedMimes)],
        ];
    }
}
