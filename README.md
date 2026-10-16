# Aurelia Boutonnière

A full-stack flower shop website with online ordering and an admin panel, built on Laravel 10 + Blade + Tailwind CSS + Alpine.js + Livewire 3.

- **Storefront:** home, shop (filter/sort/search/pagination), product detail, cart, guest checkout (Cash on Delivery or Bank Transfer with receipt upload), order confirmation + email, order tracking, about, contact.
- **Admin panel** (`/admin`): dashboard, products (rich text, multi-image gallery with drag-reorder, featured/active toggles), categories, orders (status workflow, printable invoice), custom charges (delivery fee + any other automatic/manual fees), contact messages, users (staff accounts), backups (see "Backups" below), settings (shop info, logo/favicon, delivery time slots, bank details).
- **Currency:** Maldivian Rufiyaa (MVR), displayed with a Rufiyaa sign glyph via a bundled custom webfont (at a Private Use Area codepoint — see below for why), with an image fallback for emails and the printable invoice.

---

## Tech Stack & Package Versions

| Package | Version | Purpose |
|---|---|---|
| PHP | ^8.2 | Runtime |
| laravel/framework | **10.44.0** (pinned exact) | Framework |
| laravel/breeze | ^1.29 | Admin authentication scaffolding (Blade stack) |
| livewire/livewire | ^3.0 | Reactive cart (live quantity/remove updates) |
| intervention/image | ^2.7 | Server-side image resizing & thumbnails |
| laravel/sanctum | ^3.3 | Ships with Breeze; unused beyond scaffolding (no API auth in this app) |
| tailwindcss | ^3.1 | Utility CSS, custom beige theme (`tailwind.config.js`) |
| alpinejs | ^3.4 | Mobile nav, image gallery, dynamic settings fields, checkout payment toggle |
| trix | ^2.1 | Rich-text editor for the product description field |

All versions are pinned compatible with **PHP 8.2** and **Laravel 10** — nothing here requires Laravel 11/12. Do not run `composer update` across major versions.

---

## Database Schema

```
users                 — admin/staff accounts only (no customer accounts; guest checkout)
categories             id, name, slug, description, image, sort_order, is_active
products                id, category_id→categories, title, slug, sku, description (rich HTML),
                        price, sale_price, stock_quantity, is_featured, is_active
product_images          id, product_id→products, path, thumb_path, sort_order
orders                  id, order_number (unique), name, phone, email, delivery_address,
                        delivery_date, delivery_time_slot, gift_message, special_notes,
                        payment_method (cod|bank_transfer), payment_receipt_path,
                        status (pending|confirmed|preparing|out_for_delivery|delivered|cancelled),
                        subtotal, delivery_fee, total, currency, placed_from_ip, cancelled_at
order_items              id, order_id→orders, product_id→products (nullable), product_title,
                        unit_price, quantity, line_total, gift_message, custom_flowers (nullable JSON),
                        wrapping_paper_id→wrapping_papers, wrapping_paper_name, wrapping_paper_price,
                        ribbon_id→ribbons, ribbon_name, ribbon_price (all nullable — the ids exist so
                        a cancelled order can restock the right row, see "Custom Bouquets" below)
flowers                  id, name, price_per_stem, stock_quantity, image, sort_order, is_active   —
                        the pick list for the Custom Bouquet builder (see "Custom Bouquets" below)
wrapping_papers          id, name, price, stock_quantity, image, sort_order, is_active   — same shape
                        as flowers, but a single optional pick (not a stem count) on the builder
ribbons                  id, name, price, stock_quantity, image, sort_order, is_active   — same shape as wrapping_papers
custom_charges           id, name, amount, is_automatic, sort_order, is_active   — admin-defined extra
                        fees (see "Custom Charges" below)
order_charges            id, order_id→orders, custom_charge_id→custom_charges (nullable), name,
                        amount, is_automatic   — the charges actually applied to one order
contact_messages         id, name, email, phone, subject, message, submitted_from_ip, read_at
settings                 id, key (unique), value   — admin-managed key/value store
```

An `order_items` row is either a catalogue line (`product_id` set) or a custom-bouquet line (`product_id` null, `custom_flowers`/wrapping/ribbon fields set to a snapshot of the chosen options) — see `OrderItem::isCustom()`. An order's `total` is `subtotal + delivery_fee + sum(order_charges.amount)`, recalculated server-side on every change — see `Order::recalculateTotal()`.

All tables use `utf8mb4_unicode_ci` (Laravel 10's MySQL default) so "è" in *Boutonnière* and the Rufiyaa sign store and sort correctly.

## Route Overview

```
Public:
  GET  /                               home
  GET  /shop, /shop/{product}           catalogue + detail
  GET  /custom-bouquet                  build-your-own bouquet (flower picker)
  GET  /about, /contact (+ POST)        static pages + contact form
  GET/POST/PATCH/DELETE /cart/...       session cart (/cart/add-custom for a built bouquet)
  GET/POST /checkout                    guest checkout
  GET  /order/confirmation/{order}      confirmation page
  GET/POST /track-order                 order tracking (order # + phone)
  GET  /sitemap.xml

Admin (auth + admin middleware, prefix /admin):
  /admin/login, /admin/forgot-password, ...   Breeze auth (self-registration disabled)
  /admin/dashboard
  /admin/products, /admin/categories, /admin/flowers,
  /admin/wrapping-papers, /admin/ribbons, /admin/custom-charges   resourceful CRUD
  /admin/orders, /admin/orders/{o}/invoice    list/detail/status/printable invoice
  /admin/orders/{o}/charges (+ DELETE /{orderCharge})   attach/detach a manual charge
  /admin/messages
  /admin/settings
```

Run `php artisan route:list` for the full, current list.

---

## macOS Local Setup

### 1. Install prerequisites (skip anything you already have)

```bash
# Homebrew, if you don't have it: https://brew.sh
brew install php@8.2 composer node docker docker-compose

# Make sure php 8.2 is first on PATH
echo 'export PATH="/opt/homebrew/opt/php@8.2/bin:$PATH"' >> ~/.zshrc
source ~/.zshrc

php -v        # should report PHP 8.2.x
composer -V
node -v       # v18+ is fine
php -m | grep -i gd   # GD extension must be present (Intervention Image)
```

> Note: `docker` via Homebrew installs the CLI only. Install **Docker Desktop** (or OrbStack/Colima) separately to actually run containers, then come back here.

### 2. Clone and configure

```bash
git clone <this-repo-url> aurelia
cd aurelia

cp .env.example .env
```

### 3. Start MySQL (Docker)

```bash
docker compose up -d
docker compose ps     # wait for mysql to report "healthy"
```

This starts a single MySQL 8 container matching the `.env.example` defaults (`DB_HOST=127.0.0.1`, `DB_PORT=3306`, database `aurelia`, user `aurelia`/`aurelia`) with `utf8mb4_unicode_ci` set server-wide.

### 4. Install dependencies

```bash
composer install
npm install
```

### 5. Application key, migrate, seed, storage link

```bash
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

Seeding creates: the admin account (see below), 4 categories, 12 sample products (with generated placeholder illustrations across all 4 categories), and default shop settings.

### 6. Run it

In two terminals:

```bash
php artisan serve        # http://localhost:8000
npm run dev               # Vite dev server with hot reload
```

Visit `http://localhost:8000` for the storefront and `http://localhost:8000/admin/login` for the admin panel.

For a production-style build instead of `npm run dev`, run `npm run build`.

### Default Admin Credentials

```
Email:    admin@aureliaboutonniere.test
Password: password
```

Override these before seeding a non-throwaway database by setting `ADMIN_EMAIL` / `ADMIN_PASSWORD` in `.env` first. Public self-registration is disabled (see `routes/auth.php`) — to add another admin later, run:

```bash
php artisan admin:create
```

---

## Replacing the Logo

The logo is rendered everywhere (header, footer, admin sidebar, emails, invoice, favicon) through one Blade component, `<x-logo />`, which falls back to a styled text placeholder ("*Aurelia*" in the script font + "Boutonnière" in the serif font) until a real logo is uploaded.

To swap it in: log in to the admin panel → **Settings** → **Shop Identity** → upload a **Logo** (SVG or PNG) and, optionally, a separate **Favicon**. Every place the logo appears updates immediately — no code changes needed.

The two illustrations on the About page ("Rooted in Craft" / "Delivered with Care") work the same way: **Settings** → **Site Content** → upload a photo under either image field to replace the bundled placeholder illustration (`public/images/about/`). Leave them unset to keep the defaults.

---

## The Rufiyaa Sign

Every price on the site is formatted by the single `money()` helper (`app/Support/helpers.php`) and displayed via the `<x-price :amount="..." />` component (`resources/views/components/price.blade.php`), so the currency formatting lives in exactly one place.

**On the codepoint:** the sign is rendered at **U+E900**, a Private Use Area codepoint — deliberately *not* U+20C2. An earlier pass of this project used U+20C2 on the assumption it was the assigned "Maldivian Rufiyaa Sign," but that's incorrect: Unicode's Currency Symbols block (U+20A0–U+20CF) currently ends its real assignments at U+20C0 (SOM SIGN, Unicode 9.0); there is no dedicated Unicode codepoint for the rufiyaa sign, and none has been submitted to/accepted by the Unicode Consortium. Browsers treat a genuinely unassigned codepoint specially — they refuse to let *any* font substitute a glyph for it and show their own "invalid codepoint" box instead, no matter what a custom font's `cmap` claims. This happens at the Unicode-validity layer, before font glyph lookup, so no amount of font-rebuilding fixes it at U+20C2 (verified empirically — the exact same font-building code renders perfectly at a PUA codepoint and fails identically at U+20C2, even for a placeholder rectangle glyph). The Private Use Area (U+E000–U+F8FF) is the Unicode-sanctioned mechanism for exactly this situation — a symbol with local meaning and no assigned codepoint — and is how every icon font (Font Awesome, etc.) works. If Unicode ever assigns a real codepoint for the rufiyaa sign, update `RUFIYAA_CODEPOINT` in `resources/fonts/build_rufiyaa_font.py`, `config/shop.php`'s `currency.sign`, and the `unicode-range` in `resources/css/app.css`.

No system font has a glyph at a custom PUA codepoint (by definition), so:
- **On the web**, a tiny custom webfont — `RufiyaaSign` (`public/fonts/RufiyaaSign-Regular.woff2`), built from scratch with `fontTools` and scoped via `unicode-range: U+E900` — supplies the glyph. The real UTF-8 character stays in the markup, so copy/paste and screen readers (`aria-label="MVR ..."`) still work (copying it into an app without this font installed will show as a missing-glyph box there, same tradeoff as any icon font).
- **In emails and the printable invoice**, where custom webfonts are unreliable, `<x-price for="image" />` swaps in an inline `<img>` of the sign instead (`public/images/mail/rufiyaa-sign.png`, `alt="MVR"`) — this path doesn't depend on the codepoint at all.

The webfont is generated (not hand-crafted in a font editor) by `resources/fonts/build_rufiyaa_font.py`, a small [fontTools](https://github.com/fonttools/fonttools)-based script that compiles the glyph artwork straight to TTF/WOFF2. The artwork itself is traced from a reference image of the symbol (`resources/fonts/source/rufiyaa-reference.png`) via `potrace`, producing `resources/fonts/source/rufiyaa-glyph-trace.svg`, which the build script loads, rescales, and positions on the baseline — so the glyph always matches the reference exactly rather than being hand-drawn. See the script's docstring for the full codepoint explanation and how to swap in a different reference/trace, then copy the output into `public/fonts/`. The `resources/fonts/RufiyaaSign-Regular.ttf` file in this repo is that script's own output, kept as the source of truth.

---

## Images & Storage

Uploads (product photos, category images, logo/favicon, bank transfer receipts) go through Laravel's `public` disk (`storage/app/public`, symlinked to `public/storage` via `php artisan storage:link`). Product and category photos are automatically resized and thumbnailed by `App\Services\ImageService` (Intervention Image v2, GD driver) — see `storeWithThumbnail()` / `storeSingle()`.

Image URLs (`ProductImage::url()`/`thumbUrl()`, `Category::imageUrl()`, the logo, the favicon, order receipts) are built with `asset('storage/...')` rather than `Storage::disk('public')->url()`, so they resolve against whatever host/port actually served the request instead of the `APP_URL` value baked into `.env` — images still work if you run `php artisan serve` on a non-default port, through a different local domain (Valet/Herd), etc.

**If images don't appear at all:** confirm `php artisan storage:link` has been run (step 5 above creates `public/storage` as a symlink to `storage/app/public`) and that the seeded files actually landed there (`ls storage/app/public/products`) — if that directory is empty after seeding, re-run `php artisan db:seed --class=ProductSeeder`.

---

## Custom Bouquets

Alongside the fixed catalogue (`Product`), customers can design their own bouquet at **Custom Bouquet** in the main nav: pick any mix of flower types and stem counts, add an optional gift message, and it goes through the exact same cart and checkout as a regular product.

- **Flower types** (Rose, Lily, Tulip, ...) are admin-managed under **Flowers** in the sidebar — name, price per stem, a stock quantity, a photo, active/inactive.
- **Wrapping paper** and **ribbon** (admin-managed under those same sidebar sections, `WrappingPaper`/`Ribbon`) are an *at most one* pick each — rendered as swatch buttons rather than flower's stem-count stepper — and are entirely optional.
- `App\Services\CartService` stores a custom bouquet as a `custom_flowers` + `wrapping_paper_id` + `ribbon_id` session row alongside ordinary product rows, and resolves either shape into the same display object — see the class docblock for the exact session layout.
- **Stock**: `Flower`/`WrappingPaper`/`Ribbon` each carry a `stock_quantity`, same shape as `Product`'s (`scopeInStock`, `is_low_stock`, `stock_status_label`, the admin dashboard's Low Stock Alerts widget). The Custom Bouquet builder marks an out-of-stock option unavailable; at checkout, `OrderService::placeOrder()` sums demand for each flower/wrap/ribbon *across every custom row in the cart* (the same ingredient can appear in more than one bouquet), locks those rows, and throws `InsufficientStockException` if the total exceeds what's on hand — the same guarantee `Product` already had, now covering a custom order's ingredients too. Cancelling an order restocks them; reinstating decrements again (see `OrderService::updateStatus()`).
- At checkout, `OrderService::placeOrder()` re-validates every selected flower/wrapping paper/ribbon is still active and recalculates its price from the database (never trusting the cart's stored numbers, same principle as products), then snapshots the choices onto `OrderItem` (`custom_flowers` JSON, `wrapping_paper_id`/`name`/`price`, `ribbon_id`/`name`/`price` — the ids exist purely so cancellation can restock the right row) so the order's history stays intact even if one is later renamed, repriced, or deleted.
- A deactivated-mid-checkout flower/wrapping paper/ribbon throws `App\Exceptions\ItemUnavailableException` (the custom-order analogue of `InsufficientStockException`), caught the same way in `CheckoutController`.
- A customer can attach a reference photo to a custom order at checkout (`OrderItem.reference_image_path`). It's deleted — and the column cleared — the moment the order reaches `delivered` (`OrderService::updateStatus()`): the florist has no further use for it once the order is actually fulfilled, so there's no reason to keep paying for the storage indefinitely. Cancelling, or any status short of delivered, leaves it alone.

## Custom Charges

`CustomCharge` (admin-managed under **Custom Charges**) is a named, priced extra fee with two independent modes, chosen per charge:

- **Automatic** (`is_automatic = true`): while active, added to the total of **every** order — products and custom bouquets alike. Shown to the customer on the checkout page before they place the order, not sprung on them afterward. **Delivery fee is itself just one of these** — a "Delivery Fee" charge, seeded active (see `CustomChargeSeeder`), not a separate Settings field — so admins manage its amount, and can activate/deactivate it, from this screen rather than Settings.
- **Manual** (`is_automatic = false`, the default): never applied on its own. An admin attaches it to one specific order from that order's **Charges** card (e.g. a one-off "Rush Order Fee"), which recalculates and persists that order's `total` — see `OrderService::attachCharge()` / `detachCharge()` and `Admin\OrderController::attachCharge()` / `detachCharge()`.

Every charge actually applied to an order — automatic or manually attached — becomes an `OrderCharge` row (name/amount snapshotted, like everything else on an order), so `Order::recalculateTotal()` just sums `subtotal + delivery_fee + charges`. `orders.delivery_fee` itself is always `0` on an order placed after the 2026_10_15_214900 migration — it's kept only so orders placed before that move still show their original amount (see `<x-order-totals>`), with the real fee now living in `charges` like everything else.

## Users

**Users** in the admin sidebar manages staff accounts (`App\Http\Controllers\Admin\UserController`) — every registered user is an admin (`User::isAdmin()` always returns `true`; there's no separate role yet), so this screen is really just account CRUD: name, email, password. Public self-registration stays disabled (`routes/auth.php`); this screen and `php artisan admin:create` are the two ways to add staff.

`UserPolicy::delete()` has two safety rails an admin can't bypass from the UI: you can't delete your own account (so you can't accidentally lock yourself out), and you can't delete the last remaining account (so the panel can never end up with nobody able to log in).

## Backups

**Backups** in the admin sidebar (`App\Services\BackupService`, `Admin\BackupController`) creates a single `.zip` per backup containing the full database and every file on the "public" disk (product/flower/wrapping-paper/ribbon photos, receipts, logo/favicon) — restoring one restores the whole site's state, not just rows in a table.

- **Schedule**: daily at 02:00, weekly on Monday at 02:15, monthly on the 1st at 02:30 (`App\Console\Kernel::schedule()`), each running `php artisan backup:run {type}`. This only actually fires if the host has Laravel's scheduler cron entry installed — add `* * * * * php artisan schedule:run >> /dev/null 2>&1` to the server's crontab (see Laravel's [task scheduling docs](https://laravel.com/docs/10.x/scheduling#running-the-scheduler)). Without it, nothing is missing in the code — the schedule just never gets polled.
- **Retention** (`config/backup.php`): keeps the last 5 daily, last 2 weekly, last 2 monthly — enforced by `BackupService::prune()` right after each scheduled run, per type (a daily backup never counts against weekly's or monthly's retention). A manual "Backup Now" backup is never auto-pruned; an admin deletes those by hand from the Backups screen.
- **Download**: every backup — scheduled or manual — has a Download link, streamed through the authenticated admin screen. Backups live on a private "backups" disk (`config/filesystems.php`), never the public one, since a backup contains customer names, emails, phone numbers and addresses.
- **Restore**: two paths, both destructive (they replace the live database and the live public-storage tree to exactly match the backup) and both guarded the same way — see `BackupService::performRestore()`:
  1. **From an existing backup** — Restore next to any backup already listed (auto-saved or manual).
  2. **From an uploaded backup** — the "Restore From an Uploaded Backup" form at the bottom of the screen, for a `.zip` downloaded earlier (e.g. after moving to a new server).

  Either way, a `pre-restore` safety backup of the *current* state is taken automatically, first — if that safety backup itself fails to create, the restore is aborted rather than proceeding without a way back. After swapping in the backup's database and files, `php artisan migrate --force` runs once more so the restored schema matches the current code (covers restoring a backup taken before a later migration shipped).
- **Database support**: sqlite (copies the `.sqlite` file directly) and MySQL/MariaDB (`mysqldump` / `mysql` CLI, shelled out via `Symfony\Process`) — whichever `DB_CONNECTION` is configured. A backup records which driver made it (`manifest.json` inside the zip) and restoring refuses to proceed if that doesn't match the current connection's driver, rather than attempting a cross-engine restore it can't actually do safely.

## Testing the Order Flow

1. Add a product to the cart from its detail page (optionally with a gift message).
2. Go to **Checkout**, fill in delivery details, choose **Cash on Delivery** or **Bank Transfer** (upload any image as a receipt).
3. Submit — stock is decremented and the order total is calculated **server-side only**, inside a DB transaction with row locking (`App\Services\OrderService::placeOrder()`), so two guests can't oversell the last unit of a bouquet.
4. You'll land on the confirmation page and (if `MAIL_MAILER` is configured) receive a confirmation email.
5. Track it anytime at **Track Order** with the order number + phone.
6. As the admin, open **Orders** to see it, update its status (stock is restored automatically if you cancel it), and print the invoice.

By default `.env.example` sets `MAIL_MAILER=log`, so emails are written to `storage/logs/laravel.log` instead of actually sending — swap in real SMTP credentials for production.

---

## Security Notes

- CSRF protection is on by default (Laravel's `VerifyCsrfToken` middleware, all forms use `@csrf`).
- Checkout, the contact form, and order tracking are rate-limited (`RouteServiceProvider`: `checkout`, `contact`, `track-order` limiters).
- The contact form has a honeypot field (`website`) to deflect basic bots.
- All file uploads are validated by type and size (form requests in `app/Http/Requests`).
- Admin routes require authentication **and** the `admin` middleware (`App\Http\Middleware\EnsureUserIsAdmin`); authorization is additionally centralized in policies (`app/Policies`).
- Totals are always recalculated server-side at checkout — client-submitted prices are never trusted.
- Public admin self-registration is disabled; new admins are created via `php artisan admin:create`.

---

## Folder Structure (high level)

```
app/
  Console/Commands/CreateAdminUser.php     php artisan admin:create
  Exceptions/InsufficientStockException.php
  Facades/Cart.php
  Http/Controllers/                        storefront controllers
  Http/Controllers/Admin/                  admin panel controllers
  Http/Requests/, Http/Requests/Admin/     form validation
  Http/Middleware/EnsureUserIsAdmin.php
  Livewire/CartPage.php                    reactive cart table
  Mail/                                    OrderConfirmation, ContactMessageReceived
  Models/                                  Category, Product, ProductImage, Order, OrderItem,
                                            ContactMessage, Setting, User
  Policies/
  Services/CartService.php, OrderService.php, ImageService.php
  Support/helpers.php                      money(), setting(), rufiyaa_sign(), ...
  View/Components/StorefrontLayout.php, AppLayout.php, GuestLayout.php
config/shop.php                            currency, statuses, payment methods, defaults
database/migrations/
database/seeders/                          AdminUserSeeder, SettingsSeeder, CategorySeeder,
                                            ProductSeeder (+ images/products/ seed artwork)
resources/views/
  components/                              logo, price, icon, product-card, seo, ...
  layouts/                                 storefront.blade.php, app.blade.php (admin), guest.blade.php
  pages/, shop/, cart/, checkout/, orders/ storefront pages
  admin/                                   admin panel pages
  emails/                                  markdown mail templates
  vendor/mail/                             re-themed Laravel markdown-mail components
public/fonts/RufiyaaSign-Regular.woff2
public/images/mail/rufiyaa-sign.png
docker-compose.yml                         local MySQL 8 only
```

---

## Running Tests

```bash
php artisan test
```

Uses an in-memory SQLite database (`phpunit.xml`), so no setup is needed beyond `composer install`. Covers Breeze auth/profile flows (re-pointed at `/admin/...`) plus `tests/Feature/CheckoutTest.php`, which exercises the business-critical path: adding to cart, placing a guest order, server-side total calculation, and the stock-locking guard against overselling the last unit of a product.
