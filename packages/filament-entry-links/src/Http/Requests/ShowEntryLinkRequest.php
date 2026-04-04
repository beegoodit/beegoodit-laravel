<?php

namespace BeegoodIT\FilamentEntryLinks\Http\Requests;

use BeegoodIT\FilamentEntryLinks\Support\PublicEntryViews;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Symfony\Component\HttpFoundation\Response;

class ShowEntryLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $segment = $this->route('segment');

        $this->merge([
            'segment' => is_string($segment) ? $segment : '',
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'segment' => ['required', 'string', 'max:512'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $segment = $this->route('segment');

            if (! is_string($segment) || $segment === '') {
                return;
            }

            if (! preg_match('/^[A-Za-z0-9]+(-[A-Za-z0-9-]*)?$/', $segment)) {
                $validator->errors()->add('segment', __('filament-entry-links::validation.segment_format'));
            }
        });
    }

    protected function failedValidation(Validator $validator): void
    {
        $home = config('filament-entry-links.home_url');
        $homeUrl = is_string($home) && $home !== '' ? $home : url('/');

        throw new HttpResponseException(
            response()->view(PublicEntryViews::unavailable(), [
                'homeUrl' => $homeUrl,
            ], Response::HTTP_NOT_FOUND)
        );
    }
}
