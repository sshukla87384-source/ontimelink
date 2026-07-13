# Installation Guide

## 1. Requirements

| Component | Version | Notes |
|---|---|---|
| PHP | 8.3 or newer | Extensions: `pdo_mysql`, `mbstring`, `openssl`, `ctype`, `fileinfo`, `tokenizer`, `xml`, `curl`, `json`, `bcmath` (all standard on Hostinger) |
| MySQL / MariaDB | MySQL 8.0+ / MariaDB 10.6+ | InnoDB required (row-level locking is load-bearing for points/wallet) |
| Composer | 2.x | Only needed on your **local machine** for shared hosting deploys |
| Web server | Apache with `mod_rewrite` (Hostinger default) or Nginx | |

No Node.js, Redis, Docker, Supervisor, or queue workers are required — the
application is designed for standard shared hosting.

## 2. Get the code and dependencies

```bash
cd onetimelink
composer install --no-dev --optimize-autoloader   # use plain `composer install` for local dev
```

## 3. Environment configuration

```bash
cp .env.example .env
php artisan key:generate
```

Then edit `.env`. Full variable reference:

### Application

| Variable | Purpose |
|---|---|
| `APP_NAME` | Shown in the navbar, page titles, and emails |
| `APP_ENV` | `production` on live sites (`local` for development) |
| `APP_KEY` | AES-256 key that encrypts destination URLs and sessions. **Back it up. Rotating it makes existing links unredeemable** — see `docs/SECURITY.md` |
| `APP_DEBUG` | Must be `false` in production (debug pages leak secrets) |
| `APP_URL` | Canonical HTTPS URL, e.g. `https://yourdomain.com` — used in emails, referral links, and redeem URLs |

### Database

`DB_CONNECTION=mysql`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`,
`DB_PASSWORD` — from hPanel → Databases on Hostinger (host is usually
`localhost` there).

### Sessions / cache / queue

Leave the defaults: `SESSION_DRIVER=database`, `CACHE_STORE=database`,
`QUEUE_CONNECTION=database`. These run entirely inside MySQL — nothing extra
to install. `SESSION_SECURE_COOKIE=true` requires HTTPS (free SSL is one
click on Hostinger).

### Mail

SMTP settings for verification and password-reset email. On Hostinger create
a mailbox (e.g. `no-reply@yourdomain.com`) and use `smtp.hostinger.com`,
port `465`, `MAIL_ENCRYPTION=ssl`.

### Business rules

| Variable | Default | Meaning |
|---|---|---|
| `POINTS_SIGNUP_BONUS` | 10 | Free points on registration |
| `POINTS_REFERRER_REWARD` | 10 | Points the referrer earns per verified referral |
| `POINTS_REFERRED_BONUS` | 20 | Points a referred user starts with (replaces the signup bonus) |
| `POINTS_COST_PER_LINK` | 1 | Points burned per generated link |
| `GUEST_FREE_LINKS` | 1 | Free links per (hashed) guest IP before registration is required |
| `BULK_MAX_LINKS` | 100 | Hard server-side cap per bulk request |

### Payments

| Variable | Purpose |
|---|---|
| `PAYMENT_CRYPTO_ENABLED` / `PAYMENT_WALLETPAY_ENABLED` | Toggle each gateway |
| `PAYMENT_CRYPTO_API_KEY`, `PAYMENT_CRYPTO_WEBHOOK_SECRET` | From your crypto payment processor |
| `PAYMENT_WALLETPAY_MERCHANT_ID`, `PAYMENT_WALLETPAY_API_KEY`, `PAYMENT_WALLETPAY_WEBHOOK_SECRET` | From WalletPay |

Webhook endpoints to register with the processors:
`https://yourdomain.com/webhooks/crypto` and
`https://yourdomain.com/webhooks/walletpay`. Requests are verified with an
HMAC-SHA256 `X-Signature` header over the raw body.

### Initial administrator

| Variable | Purpose |
|---|---|
| `ADMIN_EMAIL` | Required for seeding the first admin |
| `ADMIN_NAME` | Optional display name |
| `ADMIN_PASSWORD` | Optional — if omitted, a random password is generated and printed **once** during seeding |

## 4. Database setup

Create the database and a dedicated user, then run:

```bash
php artisan migrate
php artisan db:seed        # creates the admin account from ADMIN_* vars
php artisan storage:link   # public disk symlink
```

## 5. Cron (scheduler)

One standard cron entry powers link expiry and housekeeping:

```
* * * * * cd /path/to/onetimelink && php artisan schedule:run >> /dev/null 2>&1
```

The scheduler marks expired links hourly and prunes stale records daily.

## 6. Production caches

After every deploy or `.env` change:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

(Use `php artisan optimize:clear` to reset all three while debugging.)

## 7. Verify

- Visit `/` and generate a guest link, open it once (redirects), open it again (Already Redeemed).
- Register a user → verification email arrives → dashboard shows the signup bonus.
- Log into the seeded admin account → the **Admin** menu item appears.

For shared-hosting specifics (directory layout, permissions, `public_html`
strategies) continue with `docs/DEPLOYMENT_HOSTINGER.md`.
