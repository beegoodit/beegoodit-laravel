<?php

namespace BeegoodIT\FilamentUserProfile\Tests\Feature;

use BeegoodIT\FilamentUserProfile\Auth\EmailSignInCode;
use BeegoodIT\FilamentUserProfile\Enums\SignInMethod;
use BeegoodIT\FilamentUserProfile\Models\EmailSignInCode as EmailSignInCodeModel;
use BeegoodIT\FilamentUserProfile\Notifications\EmailSignInCodeNotification;
use BeegoodIT\FilamentUserProfile\Tests\TestCase;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Schema;

class EmailSignInCodeTest extends TestCase
{
    protected function defineDatabaseMigrations(): void
    {
        parent::defineDatabaseMigrations();

        $this->loadMigrationsFrom(dirname(__DIR__, 2).'/database/migrations');
    }

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('sign_in_code_users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('locale')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        config([
            'auth.providers.users.model' => SignInCodeUser::class,
            'filament-user-profile.registration' => true,
            'filament-user-profile.sign_in_methods' => ['email_code', 'password'],
            'cache.default' => 'array',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_config_defaults_to_password_only(): void
    {
        $config = file_get_contents(dirname(__DIR__, 2).'/config/filament-user-profile.php');

        $this->assertIsString($config);
        $this->assertStringContainsString("'sign_in_methods' => ['password']", $config);
    }

    public function test_panel_swaps_login_only_when_email_code_is_configured(): void
    {
        $provider = file_get_contents(dirname(__DIR__, 2).'/src/Filament/UserProfilePanelProvider.php');
        $page = file_get_contents(dirname(__DIR__, 2).'/src/Filament/Auth/SignIn.php');

        $this->assertIsString($provider);
        $this->assertIsString($page);
        $this->assertStringContainsString('SignInMethod::configured()', $provider);
        $this->assertStringContainsString('SignInMethod::EmailCode', $provider);
        $this->assertStringContainsString('->login(SignIn::class)', $provider);
        $this->assertStringContainsString('LoginResponse', $page);
    }

    public function test_configured_drops_unknown_values_and_duplicates(): void
    {
        config(['filament-user-profile.sign_in_methods' => ['email_code', 'nope', 'email_code', 'password']]);

        $this->assertSame(
            [SignInMethod::EmailCode, SignInMethod::Password],
            SignInMethod::configured(),
        );
    }

    public function test_configured_falls_back_to_password(): void
    {
        config(['filament-user-profile.sign_in_methods' => []]);
        $this->assertSame([SignInMethod::Password], SignInMethod::configured());

        config(['filament-user-profile.sign_in_methods' => ['nope']]);
        $this->assertSame([SignInMethod::Password], SignInMethod::configured());
    }

    public function test_send_does_not_create_a_user_until_the_code_is_consumed(): void
    {
        Notification::fake();

        app(EmailSignInCode::class)->send('new-player@example.com');

        $this->assertSame(0, SignInCodeUser::query()->count());

        $code = $this->sentCode();

        $user = app(EmailSignInCode::class)->consume('new-player@example.com', $code);

        $this->assertInstanceOf(SignInCodeUser::class, $user);
        $this->assertSame('new-player@example.com', $user->email);
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);
    }

    public function test_existing_user_keeps_their_password_and_mixed_case_email(): void
    {
        Notification::fake();

        $existing = SignInCodeUser::query()->create([
            'name' => 'Ada',
            'email' => 'Ada@Example.com',
            'password' => Hash::make('secret'),
        ]);

        app(EmailSignInCode::class)->send('ada@example.com');

        $user = app(EmailSignInCode::class)->consume('ada@example.com', $this->sentCode());

        $this->assertSame($existing->id, $user?->getAuthIdentifier());
        $this->assertSame(1, SignInCodeUser::query()->count());
        $this->assertSame('Ada@Example.com', $existing->fresh()->email);
        $this->assertTrue(Hash::check('secret', (string) $existing->fresh()->password));
        $this->assertNotNull($existing->fresh()->email_verified_at);
    }

    public function test_wrong_code_does_not_create_a_user_and_the_sixth_check_consumes_the_row(): void
    {
        Notification::fake();

        app(EmailSignInCode::class)->send('wrong@example.com');
        $code = $this->sentCode();
        $wrong = $code === '000000' ? '111111' : '000000';

        for ($attempt = 0; $attempt < 6; $attempt++) {
            $this->assertNull(app(EmailSignInCode::class)->consume('wrong@example.com', $wrong));
        }

        $this->assertSame(0, SignInCodeUser::query()->count());
        $this->assertNotNull(EmailSignInCodeModel::query()->first()?->consumed_at);
    }

    public function test_expired_code_is_rejected(): void
    {
        Notification::fake();

        app(EmailSignInCode::class)->send('late@example.com');
        $code = $this->sentCode();

        Carbon::setTestNow(now()->addMinutes(11));

        $this->assertNull(app(EmailSignInCode::class)->consume('late@example.com', $code));
    }

    public function test_registration_off_does_not_mail_an_unknown_address(): void
    {
        config(['filament-user-profile.registration' => false]);
        Notification::fake();

        app(EmailSignInCode::class)->send('missing@example.com');

        Notification::assertNothingSent();
        $this->assertSame(0, EmailSignInCodeModel::query()->count());
        $this->assertSame(0, SignInCodeUser::query()->count());
    }

    public function test_registration_off_still_signs_in_an_existing_user(): void
    {
        $existing = SignInCodeUser::query()->create([
            'name' => 'Ada',
            'email' => 'ada@example.com',
            'password' => Hash::make('secret'),
        ]);

        config(['filament-user-profile.registration' => false]);
        Notification::fake();

        app(EmailSignInCode::class)->send('ada@example.com');

        $user = app(EmailSignInCode::class)->consume('ada@example.com', $this->sentCode());

        $this->assertSame($existing->id, $user?->getAuthIdentifier());
    }

    protected function sentCode(): string
    {
        $code = null;

        Notification::assertSentOnDemand(
            EmailSignInCodeNotification::class,
            function (EmailSignInCodeNotification $notification) use (&$code): bool {
                $code = $notification->code;

                return strlen($notification->code) === 6;
            },
        );

        $this->assertIsString($code);

        return $code;
    }
}

class SignInCodeUser extends Authenticatable
{
    protected $table = 'sign_in_code_users';

    protected $guarded = [];
}
