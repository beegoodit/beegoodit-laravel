# Filament Social Graph

Actor-centric feeds, subscriptions, and social graph primitives for Filament applications. Add feed items, follow/unfollow flows, and home feeds with polymorphic actors (User, Team, etc.).

## Features

- **Polymorphic actors**: Any model (User, Team, etc.) can post feed items and subscribe to others via `HasSocialFeed` and `HasSocialSubscriptions`.
- **Feed items**: Subject, body (WYSIWYG rich text; stored as HTML, sanitized on display), attachments (multi-file upload stored as JSON paths; client-side preview on create/edit).
- **Subscriptions**: Subscribe/unsubscribe to actors; home feed aggregates items from subscribed feeds.
- **Entity feeds**: Feeds scoped to entities (e.g. team feed, project feed) alongside the global home feed.
- **Tenancy**: Optional team scoping for multi-tenant setups.
- **Filament Admin resources**: `FeedItemResource` and `SubscriptionResource` for CRUD. Attachments via FileUpload field on Create/Edit.
- **Livewire components**: Feed list, subscribe button (entity feeds use FeedController routes).
- **Image lightbox**: Feed item attachment images open in an in-page lightbox with gallery navigation (prev/next, arrow keys) when multiple images are in a card.

## Installation

```bash
composer require beegoodit/filament-social-graph
```

Publish and run migrations:

```bash
php artisan vendor:publish --tag=filament-social-graph-migrations
php artisan migrate
```

For tenancy, optionally publish and run the team_id migration:

```bash
php artisan vendor:publish --tag=social-graph-team-migration
php artisan migrate
```

Publish config (recommended; required for `actor_models`):

```bash
php artisan vendor:publish --tag=filament-social-graph-config
```

Publish translations to customize (optional; package includes en, de, es):

```bash
php artisan vendor:publish --tag=filament-social-graph-translations
```

**Feed page lightbox (image preview):** The feed item card opens attachment images in an in-page lightbox (overlay with prev/next for galleries, Escape to close, arrow keys when multiple images). Publish the lightbox script once so it is available; the feed content view pushes it via `@push('scripts')`:

```bash
php artisan vendor:publish --tag=filament-social-graph-assets
```

This copies `resources/js/lightbox.js` to `public/vendor/filament-social-graph/js/lightbox.js`. Ensure your feed page layout includes `@stack('scripts')`. If you use a **custom index view** (e.g. `feed_page.index_view`), include the overlay and script there: `@include('filament-social-graph::feed.partials.lightbox-overlay')` and `@push('scripts')` with `<script src="{{ asset('vendor/filament-social-graph/js/lightbox.js') }}"></script>`.

## Configuration

In `config/filament-social-graph.php`:

- **tenancy**: Enable/disable team scoping; configure `team_model` and `team_resolver`.
- **actor_models**: **Required for CRUD.** Models that can post and subscribe (e.g. `[\App\Models\User::class, \App\Models\Team::class]`). The actor selector in `FeedItemResource` and `SubscriptionResource` is hidden when empty.
- **entity_models**: Models that can have entity feeds (e.g. `[\App\Models\Team::class]`).
- **feed_page**: **layout**, **index_view** (optional app view for GET feed, e.g. breadcrumb wrapper), composer (form) visibility, **authorize_create_ability** (default `'create'`), **authorize_update_ability** (default `'update'`), **authorize_delete_ability** (default `'delete'`), **feed_item_edit_url_resolver** and **feed_item_destroy_url_resolver** (closures for Edit/Delete links on feed item cards). See Authorization.
- **attachments**: Limits for public feed create/edit forms: **max_files** (default `5`), **max_file_size_kb** (default `5120`), **allowed_mimes** (default `['jpg','jpeg','png','gif','webp','pdf']`), **multiple_upload_mode** (default `'auto'`). Used by `StoreFeedItemRequest` and `UpdateFeedItemRequest`. When Livewire’s temporary upload disk is S3, multiple file selection would normally throw `S3DoesntSupportMultipleFileUploads`; the package avoids this by using **multiple_upload_mode**: `'auto'` (default) uses per-file uploads when the temp disk is S3, otherwise native `<input multiple>`; `'native'` always uses native multiple (do not use with S3 temp); `'single_per_request'` always uses per-file uploads (e.g. for consistent UX).
- **resources.enabled**: Set to `false` to disable `FeedItemResource` and `SubscriptionResource` when registering the plugin.

**Attachment storage:** Attachments are stored as JSON paths in `feed_items.attachments`. On the **public feed** (create/edit forms) and in **Filament Admin** (FileUpload field), files are stored on the disk returned by `FeedItem::getStorageDisk()` (public or S3): directory `feed-item-attachments/`, or `feed-item-attachments/{team_id}/` when tenancy is enabled and a team is set. When a feed item is **deleted**, its attachment files are removed from storage. Ensure PHP `upload_max_filesize` and `post_max_size` are sufficient for uploads.

**Public feed body and attachments:** The public create and edit forms are **Livewire components** (FeedCreateForm and FeedEditForm). Attachments are uploaded to Livewire temporary storage (when using S3 for temp storage, each file is uploaded individually via the JS API to avoid Livewire’s S3 multi-file limitation); the combined submit only sends subject, body, and file references—avoiding PHP `post_max_size` limits when many or large files are added. The forms use a **WYSIWYG editor** (Quill via CDN) for the body; HTML is stored as-is and **sanitized on output** in the feed item card (safe tags only; script and unsafe tags are stripped). Existing items with markdown body are still rendered as markdown. The Quill editor supports **dark mode**: when a parent element has the `.dark` class (e.g. Tailwind/Flux), the package injects CSS so the toolbar and container match zinc-based form styling. The **attachment field** supports drag-and-drop: users can drop files onto the zone or click to select; file preview and remove-before-submit are shown in the component.

### Flux, Livewire and Quill

When using **Flux UI** components inside a Livewire component that also uses the **Quill** editor:

- **Avoid:** Loading Quill via `@push('styles')` / `@push('scripts')` and initialising it in `document.addEventListener('DOMContentLoaded', ...)`. That order (scripts in layout, init after DOM ready) leads to Livewire morphing the component DOM after Flux has already upgraded it; the new `<flux:*>` nodes are never upgraded, so labels and fields can disappear.
- **Use instead:** Vanilla Quill load in the **same Blade view** as the component: a `<link>` for the theme CSS, the editor container, then `<script src="...quill.js">` and an inline script that runs `new Quill(...)` immediately (no `DOMContentLoaded`). Keep everything in document order and do not push Quill assets into the layout.

The feed composer (and any similar “Livewire + Flux + Quill” form) should follow this pattern so Flux remains visible after Livewire hydration.

## Register the plugin

The plugin does **not** auto-register. Add it explicitly to the Filament panel where you want FeedItem and Subscription CRUD:

```php
// AdminPanelProvider or PortalPanelProvider
->plugins([
    \BeegoodIT\FilamentSocialGraph\Filament\FilamentSocialGraphPlugin::make(),
])
```

### Admin vs Portal panel

| Panel | Tenant context | Behavior |
|-------|----------------|----------|
| **Admin** (no `->tenant()`) | None | Platform-wide CRUD. All feed items visible. `team_id` stays `null` on create unless set via form. |
| **Portal** (with `->tenant()`) | `Filament::getTenant()` | Tenant-scoped. List/create/edit scoped to current tenant. `team_id` set automatically on create. |

`FeedItemResource` and `SubscriptionResource` use `tenantOwnershipRelationshipName = 'team'`. On a non-tenant Admin panel, tenant scoping is not applied; on a tenant-aware Portal panel, records are scoped by team.

## Package vs app responsibilities

| In package | In app |
|------------|--------|
| FeedItemResource, SubscriptionResource (List, Create, View, Edit) | Register plugin on desired panel (Admin or Portal) |
| Form schema, table columns, filters | Configure `actor_models` for your domain (User, Team, Person, etc.) |
| Userstamps on models | Publish and run migrations (including team_id if tenancy needed) |
| Translations, config defaults | Add `HasSocialFeed` / `HasSocialSubscriptions` traits to models |

## FeedItem CRUD setup checklist

1. **Install** the package and run migrations.
2. **Publish config** and set `actor_models` (required for the actor selector).
3. **Register the plugin** on your Admin or Portal panel via `->plugins([...])`.
4. **Add traits** to models that should post or subscribe.
5. If using tenancy, **publish and run** the `social-graph-team-migration`.

## Usage

### 1. Add traits to models

```php
use BeegoodIT\FilamentSocialGraph\Models\Concerns\HasSocialFeed;
use BeegoodIT\FilamentSocialGraph\Models\Concerns\HasSocialSubscriptions;

class User extends Model
{
    use HasSocialFeed, HasSocialSubscriptions;
}
```

### 2. Post and subscribe

```php
$user->createFeedItem(['body' => 'Hello world!']);
$user->subscribeTo($anotherUser);
$feed = $user->getHomeFeed(20);
$user->unsubscribeFrom($anotherUser);
```

### 3. Manage attachments in Admin

On FeedItem Create, Edit, or View, use the **Attachments** FileUpload field (Create/Edit) to add multiple files. The View page shows images as thumbnails and non-image files as download links. Body is text only (no inline images). Attachments are stored as JSON paths and appear as a gallery below the body on feed cards.

### 4. Use Livewire components

Use `<livewire:filament-social-graph.subscribe-button :feed-owner="$user" />` for subscribe/unsubscribe buttons. For entity feeds, use the FeedController routes (see §5).

### 5. Entity feed routes (REST)

For entity feeds (e.g. team feed), the preferred approach is **GET index + POST store** on the same URL using the package’s `FeedController` and `CreateFeedItemForEntity` action. The app registers the routes and binds the entity (e.g. `{team:slug}`); the package provides the controller, form request, action, and views. The form POSTs to the current URL; no `entityType`/`entityId` in the form. Feed items are created with `team_id` from server-only context (tenancy config / Filament tenant), never from request input.

**App:** Register routes in your route group (e.g. under teams):

- `GET /{team:slug}/feed` → `[FeedController::class, 'index']`
- `POST /{team:slug}/feed` → `[FeedController::class, 'store']`
- `GET /{team:slug}/feed/items/{feedItem}/edit` → `[FeedController::class, 'edit']`
- `PUT /{team:slug}/feed/items/{feedItem}` → `[FeedController::class, 'update']`
- `DELETE /{team:slug}/feed/items/{feedItem}` → `[FeedController::class, 'destroy']`

`{feedItem}` is the feed item’s id (UUID). The controller resolves the item and ensures it belongs to the entity (actor scope). Edit/delete use **authorize_update_ability** and **authorize_delete_ability** (default `'update'` and `'delete'`). To show Edit/Delete links on feed item cards, set **feed_item_edit_url_resolver** and **feed_item_destroy_url_resolver** in `feed_page` to closures that accept a `FeedItem` and return the edit/destroy URL (or `null` to hide).

Optionally set `feed_page.index_view` in config to an app view that wraps the package feed content (e.g. breadcrumb + `@include('filament-social-graph::feed.content', ['entity' => $entity, 'showComposer' => $showComposer])`).

### 6. Authorization (composer visibility and create)

Whether the feed composer is shown on an entity feed (e.g. team feed) and whether a user can create a feed item there is controlled by Laravel’s Gate/policy. The package ships a default **FeedItemPolicy** with a `create(?User $user, $entity)` method: guests cannot create; authenticated users can create for the global feed (`$entity` null); for entity feeds, creation is allowed only when the entity’s morph class is in `actor_models`. The package registers this policy for `FeedItem::class`, so no app setup is required for the default behavior.

To restrict who can post (e.g. only team members), register your own policy in `AppServiceProvider` or `AuthServiceProvider`:

```php
use BeegoodIT\FilamentSocialGraph\Models\FeedItem;

Gate::policy(FeedItem::class, \App\Policies\FeedItemPolicy::class);
```

Implement `create($user, $entity)`: return `true` when the user may create a feed item for that entity (or for the global feed when `$entity` is null). The same check is used to show/hide the composer and to authorize `FeedController::store`.

**Example: restrict team feed posting to team owner/admin.** For entity feeds where the entity is a Team, allow creation only when the user has team owner/admin access (e.g. via `$user->isTeamAdmin($entity)`). In your app policy:

```php
public function create(?Authenticatable $user, mixed $entity = null): bool
{
    if ($user === null) {
        return false;
    }
    if ($entity === null) {
        return true; // global feed
    }
    if (! $entity instanceof \Illuminate\Database\Eloquent\Model) {
        return false;
    }
    if ($entity instanceof \App\Models\Team) {
        return $user instanceof \App\Models\User && $user->isTeamAdmin($entity);
    }
    // Other entities: e.g. allow when in config('filament-social-graph.actor_models')
    $actorModels = config('filament-social-graph.actor_models', []);
    return in_array($entity->getMorphClass(), $actorModels, true);
}
```

Register the policy in `AppServiceProvider::boot()` as shown above so it overrides the package default.

---

Part of the BeegoodIT shared package ecosystem.
