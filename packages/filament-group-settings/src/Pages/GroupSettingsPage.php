<?php

declare(strict_types=1);

namespace BeegoodIT\FilamentGroupSettings\Pages;

use BackedEnum;
use BeegoodIT\FilamentGroupSettings\GroupSettingsPlugin;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\CanUseDatabaseTransactions;
use Filament\Pages\Page;
use Filament\Panel;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Livewire\Attributes\Locked;
use Throwable;

/**
 * @property-read Schema $form
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class GroupSettingsPage extends Page
{
    use CanUseDatabaseTransactions;

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * @var ?Model
     */
    #[Locked]
    public $tenant = null;

    protected static bool $isDiscovered = false;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 99;

    abstract public static function getGroupKey(): string;

    public static function getSlug(?Panel $panel = null): string
    {
        return static::getGroupKey().'/settings';
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-group-settings::messages.Settings');
    }

    public function getTitle(): string|Htmlable
    {
        return static::getNavigationLabel();
    }

    /**
     * @return array<string>
     */
    public function getBreadcrumbs(): array
    {
        $breadcrumbs = [];
        $group = static::getNavigationGroup();

        if (is_string($group) && $group !== '') {
            $breadcrumbs[] = $group;
        }

        $breadcrumbs[] = static::getNavigationLabel();

        return $breadcrumbs;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::isRegisteredOnPlugin();
    }

    public static function canAccess(): bool
    {
        $tenant = Filament::getTenant();

        if (! auth()->check() || ! $tenant instanceof Model) {
            return false;
        }

        if (! static::isRegisteredOnPlugin()) {
            return false;
        }

        return static::canManage($tenant);
    }

    /**
     * Override when the host app has roles (authorize the whole page). Default: any member who can open the panel.
     */
    public static function canManage(Model $tenant): bool
    {
        return $tenant->exists;
    }

    /**
     * @param  array<Tab>  $tabs
     */
    protected function topicTabs(array $tabs): Tabs
    {
        return Tabs::make()
            ->contained(false)
            ->tabs($tabs);
    }

    public function mount(): void
    {
        $this->tenant = $this->resolveRecord();

        $this->fillForm();
    }

    public function defaultForm(Schema $schema): Schema
    {
        return $schema
            ->operation('edit')
            ->model($this->tenant)
            ->statePath('data');
    }

    public function form(Schema $schema): Schema
    {
        return $schema;
    }

    public function save(): void
    {
        try {
            $this->beginDatabaseTransaction();

            $data = $this->form->getState();
            $this->handleRecordUpdate($this->tenant, $data);
        } catch (Halt $exception) {
            $exception->shouldRollbackDatabaseTransaction()
                ? $this->rollBackDatabaseTransaction()
                : $this->commitDatabaseTransaction();

            return;
        } catch (Throwable $exception) {
            $this->rollBackDatabaseTransaction();

            throw $exception;
        }

        $this->commitDatabaseTransaction();

        $this->getSavedNotification()?->send();

        if ($redirectUrl = $this->getRedirectUrl()) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode($redirectUrl));
        }
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                $this->getFormContentComponent(),
            ]);
    }

    public function getFormContentComponent(): Component
    {
        return Form::make([EmbeddedSchema::make('form')])
            ->id('form')
            ->livewireSubmitHandler('save')
            ->footer([
                Actions::make($this->getFormActions())
                    ->key('form-actions'),
            ]);
    }

    /**
     * @return array<Action|ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament-panels::pages/tenancy/edit-tenant-profile.form.actions.save.label'))
                ->submit('save')
                ->keyBindings(['mod+s']),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $record->fill($data)->save();

        return $record;
    }

    protected function fillForm(): void
    {
        $keys = collect($this->form->getFlatFields(withHidden: true))
            ->map(fn (Field $field): string => $field->getName())
            ->unique()
            ->all();

        $this->form->fill(Arr::only($this->tenant->attributesToArray(), $keys));
    }

    protected function resolveRecord(): Model
    {
        $record = Filament::getTenant();

        abort_unless($record instanceof Model, 404);

        return $record;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title(__('filament-group-settings::messages.Saved'));
    }

    protected function getRedirectUrl(): ?string
    {
        return null;
    }

    protected static function isRegisteredOnPlugin(): bool
    {
        try {
            return GroupSettingsPlugin::get()->hasPage(static::class);
        } catch (Throwable) {
            return false;
        }
    }
}
