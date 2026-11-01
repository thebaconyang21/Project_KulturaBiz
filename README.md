# KulturaBiz

KulturaBiz is a Laravel marketplace connecting customers with Mindanaoan artisans. Customers can discover handmade products and cultural stories, place and track orders, and review delivered purchases. Artisans manage products and orders after admin approval.

## Features

- Public product catalog with search, category, price, and sort filters
- Artisan storefronts and cultural heritage stories
- Customer registration, shopping cart, checkout, order tracking, and reviews
- Artisan approval, product management, and sales views
- Admin tools for managing users, products, categories, and orders
- Cash on Delivery and configurable online payment modes

## Technology

- PHP 8.2+
- Laravel 12
- MySQL/MariaDB (recommended for deployment; SQLite can be used locally)
- Node.js and npm for Vite/Tailwind assets
- Blade, Tailwind CSS, and Alpine.js

## Local development setup

1. Install PHP 8.2 or newer with the required Laravel PHP extensions, Composer, Node.js, and npm.
2. Install PHP dependencies:

   ```sh
   composer install
   ```

3. Create your local environment file and generate an application key:

   ```sh
   cp .env.example .env
   php artisan key:generate
   ```

   On PowerShell, use `Copy-Item .env.example .env` instead of `cp`.

4. Configure `.env` for your local database. Create the database first. For example:

   ```dotenv
   APP_NAME=KulturaBiz
   APP_ENV=local
   APP_DEBUG=true
   APP_URL=http://127.0.0.1:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=kulturabiz
   DB_USERNAME=root
   DB_PASSWORD=
   ```

   Use credentials appropriate for your machine. Keep `.env` private; never commit it or put production credentials in source control.

5. Run database migrations:

   ```sh
   php artisan migrate
   ```

6. Install frontend dependencies and compile assets:

   ```sh
   npm install
   npm run build
   ```

7. (Optional) Load sample products, stories, and accounts for local development:

   ```sh
   php artisan db:seed
   ```

   The seeder uses demo credentials, including the default password `password`. Seed only a disposable local database. Never seed demo accounts into production; use a unique, strong password for any production administrator.

8. Start the Laravel development server:

   ```sh
   php artisan serve
   ```

   Open `http://127.0.0.1:8000`. For frontend hot reload during development, run `npm run dev` in a second terminal.

## Environment configuration

At minimum, review these values for each environment:

| Variable | Local development | Production |
|---|---|---|
| `APP_ENV` | `local` | `production` |
| `APP_DEBUG` | `true` | `false` |
| `APP_URL` | Local URL | Public HTTPS URL |
| `APP_KEY` | Generate with `php artisan key:generate` | Generate and keep secret; preserve it across deployments |
| `DB_*` | Local database credentials | Dedicated production database credentials |
| `LOG_LEVEL` | `debug` as needed | Usually `warning` or higher |
| `MAIL_*` | `log` or local mail catcher | Configured SMTP or transactional mail provider |
| `FILESYSTEM_DISK` | Local disk | Persistent storage; see [Product image storage](#product-image-storage) |

Never expose `.env`, application keys, payment credentials, mail passwords, or cloud-storage keys in public repositories, screenshots, logs, or client-side code.

## Payment modes

- Cash on Delivery is available independently of PayMongo.
- GCash, Maya, and bank transfer can be shown as demo options when payment simulation is enabled. Demo mode is not connected to a payment provider and must never be used to collect real payments.
- `PAYMENT_SIMULATION_ENABLED` controls demo options. The default is enabled outside production and disabled in production. Set it explicitly for every environment.
- Online PayMongo payment support is controlled by `USE_REAL_PAYMENT`. Configure provider credentials and verify the complete integration before enabling it.

Example local demo configuration:

```dotenv
USE_REAL_PAYMENT=false
PAYMENT_SIMULATION_ENABLED=true
```

For production, keep simulation disabled and only enable online payments after the PayMongo account, credentials, allowed payment methods, return URL, webhook endpoint, signature verification, failure handling, and refund process have all been tested in the provider's test environment:

```dotenv
USE_REAL_PAYMENT=false
PAYMENT_SIMULATION_ENABLED=false
PAYMONGO_PUBLIC_KEY=
PAYMONGO_SECRET_KEY=
PAYMONGO_WEBHOOK_SECRET=
```

After confirming the integration is production-ready, set `USE_REAL_PAYMENT=true` and supply the correct live credentials through your hosting provider's secret manager or protected environment configuration. Never use live credentials in local development or commit them to Git. If real payment setup fails, leave online payments disabled and offer Cash on Delivery until the issue is resolved.

## Email, notifications, and courier

Laravel mail settings use `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS`, and `MAIL_FROM_NAME`. Locally, `MAIL_MAILER=log` writes mail output to the application log instead of delivering it. In production, configure a verified SMTP or transactional-mail provider, then send and verify a test message.

The project also has configuration switches for external services:

- `USE_REAL_NOTIFICATIONS` and the Twilio/Mailgun credentials
- `USE_REAL_COURIER`, `COURIER_PROVIDER`, `COURIER_API_KEY`, and related courier settings

Confirm these integrations are implemented, credentials are valid, failure paths are handled, and delivery is tested before enabling them. Do not promise customers live SMS, email, or courier tracking while running the simulated service.

## Product image storage

Artisan product and story images are uploaded to Laravel's `public` filesystem disk. On a single server with persistent local storage, create the public storage link:

```sh
php artisan storage:link
```

For production, ensure the underlying public storage is persistent and backed up. Ephemeral application containers can lose locally stored uploads during deployments. If using S3 or another object store, update and verify the upload code to use that disk; setting `FILESYSTEM_DISK=s3` alone does not change code that explicitly selects the `public` disk. Test image uploads, display URLs, access controls, and backup/restore before launch.

## Tests

Run the automated test suite with:

```sh
php artisan test
```

The tests cover registration and role access, artisan product ownership, cart and checkout inventory, product visibility, reviews, and simulated/PayMongo payment outcomes. Payment tests use a test database and mocked provider responses; they do not prove that live payment credentials or external provider configuration are correct.

## Deployment

Use a supported PHP 8.2+ production runtime and a maintained database service. Point the web server document root to Laravel's `public` directory; do not expose the repository root, `.env`, `storage`, or other application files directly.

Typical release steps (adapt them to the host and release strategy):

```sh
composer install --no-dev --prefer-dist --optimize-autoloader
npm ci
npm run build
php artisan down
php artisan migrate --force
php artisan storage:link
php artisan optimize
php artisan up
```

Run release commands from the correct application directory. Keep a database backup before migrations, and take care with maintenance mode on hosts that use immutable releases. Configure the web server, HTTPS, environment variables, writable `storage` and `bootstrap/cache` directories, queue workers if jobs are enabled, and restart long-running workers after deployment.

## Production go-live checklist

- [ ] Set `APP_ENV=production`, `APP_DEBUG=false`, the correct HTTPS `APP_URL`, and a stable secret `APP_KEY`.
- [ ] Use a production database with least-privilege credentials, encrypted connections where available, and no demo seed data.
- [ ] Take automated, encrypted database backups. Define retention and recovery objectives, and periodically test restoring a backup.
- [ ] Back up uploaded product/story images and verify they survive deployments. Use persistent storage or a properly configured object-storage disk.
- [ ] Configure production email delivery and sender-domain authentication; send a test message and monitor bounces and delivery errors.
- [ ] Configure error monitoring and alerting. Review Laravel logs, rotate/retain them, and ensure secrets and payment data are not logged.
- [ ] Keep payment simulation disabled. Test PayMongo in test mode, confirm valid and invalid webhook behavior, secure webhook secrets, and verify paid, failed, and abandoned payment flows before enabling live mode.
- [ ] Confirm payment confirmation is based on trusted provider verification, not a browser redirect alone. Document reconciliation, refunds, and support procedures.
- [ ] Enable real courier or notification services only after credentials, endpoint behavior, timeouts, and error handling have been verified.
- [ ] Serve the site over HTTPS, restrict inbound access, keep dependencies and the server patched, and verify production file permissions.
- [ ] Run the complete test suite and a manual end-to-end smoke test for registration, artisan approval, product upload, checkout, order status, and email delivery.
- [ ] Check the deployment and rollback plan, database migration impact, storage capacity, and operational ownership before launch.

## Further reading

- [Laravel 12 documentation](https://laravel.com/docs/12.x)
- [PayMongo developer documentation](https://developers.paymongo.com/)
