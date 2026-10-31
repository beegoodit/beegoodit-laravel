<?php

namespace BeegoodIT\FilamentUserProfile\Auth;

use BeegoodIT\FilamentUserProfile\Enums\SignInMethod;
use BeegoodIT\FilamentUserProfile\Models\EmailSignInCode as EmailSignInCodeModel;
use BeegoodIT\FilamentUserProfile\Notifications\EmailSignInCodeNotification;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use SensitiveParameter;

class EmailSignInCode
{
    public function send(string $email): void
    {
        $email = $this->normalizeEmail($email);
        $this->assertEmailCodeEnabled();

        $user = $this->findUser($email);

        $key = 'email-sign-in-code:'.sha1($email.'|'.(string) request()->ip());

        if (RateLimiter::tooManyAttempts($key, 3)) {
            throw ValidationException::withMessages([
                'data.email' => __('filament-user-profile::auth.email_code.throttled', [
                    'seconds' => RateLimiter::availableIn($key),
                ]),
            ]);
        }

        RateLimiter::hit($key, 10 * 60);

        if (! config('filament-user-profile.registration') && ! $user instanceof Authenticatable) {
            return;
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        DB::transaction(function () use ($email, $code): void {
            EmailSignInCodeModel::query()
                ->where('email', $email)
                ->whereNull('consumed_at')
                ->update(['consumed_at' => now()]);

            EmailSignInCodeModel::query()->create([
                'email' => $email,
                'code_hash' => Hash::make($code),
                'expires_at' => now()->addMinutes(10),
            ]);
        });

        Notification::route('mail', $email)->notify(new EmailSignInCodeNotification($code));
    }

    public function consume(string $email, #[SensitiveParameter] string $code): ?Authenticatable
    {
        $email = $this->normalizeEmail($email);

        if (in_array(preg_match('/^\d{6}$/', $code), [0, false], true)) {
            $this->checkAgainstDummyHash($code);

            return null;
        }

        return DB::transaction(function () use ($email, $code): ?Authenticatable {
            $record = EmailSignInCodeModel::query()
                ->where('email', $email)
                ->whereNull('consumed_at')
                ->where('expires_at', '>', now())
                ->latest('id')
                ->lockForUpdate()
                ->first();

            if (! $record instanceof EmailSignInCodeModel) {
                $this->checkAgainstDummyHash($code);

                return null;
            }

            if ($record->attempts >= 5) {
                $record->forceFill(['consumed_at' => now()])->save();

                return null;
            }

            $record->increment('attempts');

            if (! Hash::check($code, $record->code_hash)) {
                if ($record->attempts >= 5) {
                    $record->forceFill(['consumed_at' => now()])->save();
                }

                return null;
            }

            $record->forceFill(['consumed_at' => now()])->save();

            $user = $this->findUser($email);

            if ($user instanceof Authenticatable) {
                if ($user->email_verified_at === null) {
                    $user->forceFill(['email_verified_at' => now()])->save();
                }

                return $user;
            }

            if (! config('filament-user-profile.registration')) {
                return null;
            }

            return $this->createUser($email);
        });
    }

    protected function assertEmailCodeEnabled(): void
    {
        if (SignInMethod::enabled(SignInMethod::EmailCode)) {
            return;
        }

        throw ValidationException::withMessages([
            'data.email' => __('filament-user-profile::auth.email_code.invalid'),
        ]);
    }

    protected function normalizeEmail(string $email): string
    {
        $email = Str::lower(trim($email));

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($email) > 255) {
            throw ValidationException::withMessages([
                'data.email' => __('validation.email', ['attribute' => 'email']),
            ]);
        }

        return $email;
    }

    protected function findUser(string $email): ?Authenticatable
    {
        /** @var class-string<Model> $model */
        $model = config('auth.providers.users.model');

        $user = $model::query()->whereRaw('LOWER(email) = ?', [$email])->orderBy('id')->first();

        return $user instanceof Authenticatable ? $user : null;
    }

    protected function createUser(string $email): Authenticatable
    {
        /** @var class-string<Model&Authenticatable> $model */
        $model = config('auth.providers.users.model');

        $user = new $model;
        $attributes = [
            'name' => $this->nameFromEmail($email),
            'email' => $email,
            'password' => null,
            'email_verified_at' => now(),
        ];

        if (Schema::hasColumn($user->getTable(), 'locale')) {
            $locale = session('locale', app()->getLocale());
            $attributes['locale'] = is_string($locale) && $locale !== '' ? $locale : null;
        }

        $user->forceFill($attributes);

        try {
            $user->save();
        } catch (UniqueConstraintViolationException $exception) {
            $existing = $this->findUser($email);

            if ($existing instanceof Authenticatable) {
                return $existing;
            }

            throw $exception;
        }

        return $user;
    }

    protected function nameFromEmail(string $email): string
    {
        $local = Str::before($email, '@');
        $name = Str::of($local)->replace(['.', '_', '-', '+'], ' ')->squish()->title()->toString();

        if ($name === '') {
            return $email;
        }

        return Str::limit($name, 255, '');
    }

    /**
     * Spend the same hash work when no row matches, so a missing code is not obvious from timing.
     */
    protected function checkAgainstDummyHash(#[SensitiveParameter] string $code): void
    {
        static $dummyHash = null;

        $dummyHash ??= Hash::make('000000');

        Hash::check($code, $dummyHash);
    }
}
