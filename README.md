# CrowHub Backend

CrowHub is a Laravel 12 + Filament 4 application that powers the internal CRM, quoting, and maintenance operations for field activation teams. The `/admin` panel exposes authoring tools while `/api` exposes a small public surface for future marketing sites.

## Requirements

- PHP 8.2+
- Composer
- Node 18+ / npm (for Vite assets)
- SQLite, MySQL, or PostgreSQL

## Local Setup

1. Copy the environment file and fill in database credentials:
   ```bash
   cp .env.example .env
   ```
2. Install dependencies:
   ```bash
   composer install
   npm install
   ```
3. Generate an application key:
   ```bash
   php artisan key:generate
   ```
4. Run migrations and seed demo data (products, leads, quotes, maintenance contracts, and an admin account):
   ```bash
   php artisan migrate --seed
   ```
5. Build frontend assets (Vite) for development:
   ```bash
   npm run dev
   ```
6. Serve the application:
   ```bash
   php artisan serve
   ```

## Admin Access

- URL: `http://localhost:8000/admin`
- Email: `admin@crowhub.test`
- Password: `password`

The admin user has a `Profile` with the `admin` role and can access all Filament resources (Products, Leads, Quotes, Payments, Maintenance Contracts) plus the dashboard widgets showing sales and maintenance health.

## Seeded Demo Data

`php artisan db:seed` creates:

- 3 sample products with feature lists and pricing hints.
- 2 leads with notes, one of which has a sent quote, quote items, and a payment.
- 1 maintenance contract with an associated payment, so dashboard widgets surface due/overdue state.
- The Filament admin user noted above.

You can safely re-run the seeder; records are updated idempotently.

## Public API

All public routes live under `/api` and support configurable CORS via `FRONTEND_ORIGIN` (defaults to `https://frontend.crowhub.test`).

| Method & Path             | Description                                                                 | Notes                                                                  |
|--------------------------|-----------------------------------------------------------------------------|------------------------------------------------------------------------|
| `GET /api/products`      | Returns a list of active products (name, slug, description, features, pricing hint). | Only `is_active = true` products are returned.                         |
| `GET /api/products/{slug}` | Returns a single active product matching the slug.                            | Inactive or unknown slugs return `404`.                                |
| `POST /api/inquiries`    | Creates/updates a lead, logs an inquiry, and links to an optional product.   | Body: `name`, `email?`, `phone?`, `company?`, `product_slug?`, `message`. Rate limited to **10 requests/min per IP**. Responds with `{ lead_id, inquiry_id }`. Existing leads are reused by email/phone and are not downgraded from `won`/`lost`. Lead source is set to `product` when a `product_slug` is supplied, otherwise `website`. |

Set `FRONTEND_ORIGIN` in `.env` if you need to allow a different domain for the marketing SPA.

## Running Tests

Use the built-in Feature tests to validate the API surface and default Laravel auth flows:

```bash
php artisan test
```

## Useful Commands

- Recalculate quote totals manually (Filament uses this via action buttons):
  ```bash
  POST /admin/quotes/{quote}/recalculate
  ```
- Run the queue worker/UI stack for local dev:
  ```bash
  npm run dev
  php artisan serve
  ```

## Next Steps

- Update `FRONTEND_ORIGIN` before deploying to production.
- Configure mail settings in `.env` if you plan to send notifications from Filament actions.
- Review the `database/seeders/DatabaseSeeder.php` for the exact data footprint.
