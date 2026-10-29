<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

In addition, [Laracasts](https://laracasts.com) contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

You can also watch bite-sized lessons with real-world projects on [Laravel Learn](https://laravel.com/learn), where you will be guided through building a Laravel application from scratch while learning PHP fundamentals.

## Agentic Development

Laravel's predictable structure and conventions make it ideal for AI coding agents like Claude Code, Cursor, and GitHub Copilot. Install [Laravel Boost](https://laravel.com/docs/ai) to supercharge your AI workflow:

```bash
composer require laravel/boost --dev

php artisan boost:install
```

Boost provides your agent 15+ tools and skills that help agents build Laravel applications while following best practices.

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Parcel claim card verification

Payment operations use a provider adapter selected by `PAYMENT_GATEWAY`. The admin settings page displays available adapters and the active provider. The payment service contract is provider-neutral; each provider adapter owns its API requests, webhook signature validation, hosted checkout, authorization tokens, and refunds. Paystack is the adapter included currently. Adding another provider requires implementing `App\Contracts\PaymentGateway` and registering it in `config/payments.php`; the panel and ledger then render the provider label from that registry.

Set `PAYMENT_GATEWAY=paystack` and configure that adapter's secret in `.env` with `PAYSTACK_SECRET_KEY`. General payment defaults use `PAYMENT_CURRENCY`, `VERIFICATION_CURRENCY`, `VERIFICATION_AMOUNT`, and `REFUND_VERIFICATION`. Existing `PAYSTACK_*` currency variables remain fallback compatible. Set `PAYMENTS_ADMIN_EMAIL` to the authorized staff account, run `php artisan migrate`, and configure the active provider's webhook URL at `/webhooks/{gateway-key}`. For the included adapter that is `/webhooks/paystack`.

Transactions and saved authorizations store a generic gateway key and reference. Existing Paystack records are preserved by the gateway-column migration, and refunds or saved-card charges continue through the same provider that created the record. Authorization codes are encrypted using Laravel's application key; full card numbers and CVVs never pass through this application.

The admin workspace includes claim CRUD and bulk status updates, profile and payment settings, saved-card removal, transaction details, charges, and refunds. Transactions remain append-only for audit history.

## Customer billing and subscriptions

Open **Admin → Billing** to create one-time or recurring plans, then assign a plan to a claim with a reusable saved card. Record the customer's specific agreement (price, currency, interval, and cancellation terms) and confirm consent before starting billing. Existing subscriptions keep a snapshot of their agreed amount and interval when a plan is edited. Staff can charge now, pause, resume, or cancel a subscription; declined charges enter a retry schedule, while uncertain gateway timeouts stay pending for webhook/reconciliation to avoid duplicate charges.

The due-billing command is `php artisan billing:charge-due`. Laravel schedules it every minute in `routes/console.php`; production must run Laravel's scheduler every minute (for example, a cron entry invoking `php /path/to/application/artisan schedule:run`). For local development, run `php artisan schedule:work`. The configured gateway must have a webhook at `/webhooks/{gateway-key}` so asynchronous charge results can settle billing attempts.

## cPanel deployment over FTP/FTPS

The domain's cPanel document root is `/claims.updrivehub.com/public`, so deploy the **whole Laravel project** to its parent directory, `/claims.updrivehub.com/`. The workflow is `.github/workflows/deploy.yml`; it builds Composer production dependencies and Vite assets on GitHub, then transfers them over explicit FTPS. It does not need SSH and does not upload `.env`, local databases, logs, sessions, cached views, tests, or `node_modules`.

Add these repository Actions secrets before running the workflow:

- `FTP_SERVER`: the FTP hostname from cPanel (not the website URL).
- `FTP_USERNAME` and `FTP_PASSWORD`: a dedicated FTP account with access to the app's parent directory.
- `FTP_SERVER_DIR`: `/claims.updrivehub.com/` as shown in cPanel. If the FTP account is jailed to a subdirectory, use that account's corresponding relative path instead.
- `FTP_PORT` is optional; it defaults to `21`.

Push to `main` or run **Build and deploy Parcel Claims** from GitHub Actions. Use an FTPS-capable cPanel FTP account. If the host only supports implicit FTPS, change the workflow protocol to `ftps-legacy`.

Before opening the live site, use cPanel File Manager to create the production `.env` in the Laravel root (beside `artisan`), configure the production MySQL connection, `APP_KEY`, `APP_URL=https://claims.updrivehub.com`, `APP_DEBUG=false`, `PAYMENTS_ADMIN_EMAIL`, and the active gateway credentials. Select PHP 8.3 or later in cPanel. The deployment workflow does not run production migrations: create the database and user in cPanel and apply the Laravel migrations to that database through the hosting account's available database tooling. Ensure `storage/` and `bootstrap/cache/` are writable by PHP, and keep the domain document root pointed at the project's `public/` folder. Do not move the document root to the Laravel project root.

The workflow prepares package manifests using an ephemeral SQLite file on the GitHub runner; that file is excluded from FTP upload. It does not send database credentials or a local `.env` to GitHub or the server.

