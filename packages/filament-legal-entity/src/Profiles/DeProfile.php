<?php

namespace BeegoodIT\FilamentLegalEntity\Profiles;

use BeegoodIT\FilamentLegalEntity\Enums\LegalForm;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

final class DeProfile implements CountryProfile
{
    public function key(): string
    {
        return 'DE';
    }

    /**
     * @return list<string>
     */
    public function allowedKeys(): array
    {
        return [
            'legal_form',
            'legal_form_other',
            'representative',
            'vat_id',
            'register_court',
            'register_number',
            'founded_at',
        ];
    }

    /**
     * @param  array<string, mixed>  $profile
     * @return array<string, mixed>
     */
    public function normalize(array $profile): array
    {
        $unknown = array_diff(array_keys($profile), $this->allowedKeys());

        if ($unknown !== []) {
            throw ValidationException::withMessages([
                'profile' => __('filament-legal-entity::entity.unknown_profile_keys', [
                    'keys' => implode(', ', $unknown),
                ]),
            ]);
        }

        $legalForm = LegalForm::tryFrom((string) ($profile['legal_form'] ?? ''));
        $requiresRegister = $legalForm?->requiresRegister() ?? false;

        $validator = Validator::make($profile, [
            'legal_form' => ['required', 'string', Rule::enum(LegalForm::class)],
            'legal_form_other' => [
                Rule::requiredIf(fn (): bool => $legalForm === LegalForm::Other),
                'nullable',
                'string',
                'max:255',
            ],
            'representative' => ['required', 'string', 'max:255'],
            'vat_id' => ['nullable', 'string', 'max:32'],
            'register_court' => [
                Rule::requiredIf(fn (): bool => $requiresRegister),
                'nullable',
                'string',
                'max:255',
            ],
            'register_number' => [
                Rule::requiredIf(fn (): bool => $requiresRegister),
                'nullable',
                'string',
                'max:255',
            ],
            'founded_at' => ['nullable', 'date'],
        ]);

        $validated = $validator->validate();

        if (($validated['founded_at'] ?? null) !== null) {
            $validated['founded_at'] = \Illuminate\Support\Facades\Date::parse($validated['founded_at'])->toDateString();
        }

        if ($legalForm !== LegalForm::Other) {
            unset($validated['legal_form_other']);
        }

        if ($legalForm !== null && ! $legalForm->showsRegisterFields()) {
            unset($validated['register_court'], $validated['register_number']);
        }

        return Arr::whereNotNull($validated);
    }
}
