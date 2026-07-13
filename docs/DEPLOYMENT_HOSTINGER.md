# Hostinger Shared Hosting Deployment

This guide targets **Hostinger Premium / Business shared hosting** (hPanel).
No VPS, SSH root, Docker, Redis, Node, or long-running workers are needed —
only PHP 8.3, MySQL, and one cron job.

## 0. Before you start

1. hPanel → **Advanced → PHP Configuration** → select **PHP 8.3** and make
   sure `pdo_mysql`, `mbstring`, `openssl`, `curl`, and `fileinfo` are enabled
   (they are by default).
2. hPanel → **Databases → MySQL Databases** → create a database + user, note
   the credentials. Host is `localhost`.
3. hPanel → **Security → SSL** → install the free SSL certificate and enable
   **Force HTTPS**.
4. Create a `no-reply@yourdomain.com` mailbox under **Emails** for SMTP.

## 1. Build locally, upload

Shared hosting has no reliable Composer runtime for large installs, so build
the release on your machine:

```bash
composer install --no-dev --optimize-autoloader
```

Zip the whole project **including `vendor/`** and upload it (File Manager or
SFTP — Hostinger provides SSH/SFTP on these plans).

## 2. Choose a directory strategy

### Strategy A — recommended: app above the web root

```
/home/uXXXX/
├── onetimelink/          ← the whole project (NOT web-accessible)
└── domains/yourdomain.com/
    └── public_html/      ← contents of onetimelink/public
```

1. Upload/extract the project to `~/onetimelink`.
2. Copy everything inside `onetimelink/public/` into `public_html/`
   (including `.htaccess`).
3. Edit `public_html/index.php` and point the two paths at the app:

   ```php
   require __DIR__.'/../../../onetimelink/vendor/autoload.php';
   $app = require_once __DIR__.'/../../../onetimelink/bootstrap/app.php';
   ```

   (Adjust the `../` count to match your actual layout.)

This keeps `.env`, `storage/`, and all source code outside the web root —
the strongest posture.

### Strategy B — whole project inside `public_html`

If your plan/setup forces the project into `public_html`, extract it there
as-is. The included **root `.htaccess`** rewrites all traffic into `public/`
and denies direct access to `.env`, `composer.*`, and `artisan` as a
fallback. Strategy A is still preferred whenever possible.

## 3. Configure

```bash
# via SSH (hPanel → Advanced → SSH Access), inside the project directory:
cp .env.example .env
nano .env            # fill in APP_URL, DB_*, MAIL_*, ADMIN_EMAIL, payment keys
php artisan key:generate
```

No SSH? Edit `.env` in File Manager and generate a key locally with
`php artisan key:generate --show`, then paste it into `APP_KEY`.

## 4. Migrate, seed, link storage

```bash
php artisan migrate --force
php artisan db:seed --force        # prints the generated admin password ONCE — save it
php artisan storage:link
```

If `storage:link` fails because symlinks are restricted, create it from PHP
once via hPanel's **Fix File Ownership**/File Manager, or run:

```bash
ln -s ../onetimelink/storage/app/public public_html/storage
```

## 5. File permissions

```bash
find storage bootstrap/cache -type d -exec chmod 775 {} \;
find storage bootstrap/cache -type f -exec chmod 664 {} \;
chmod 600 .env
```

Everything else can stay at Hostinger's defaults (755 dirs / 644 files).

## 6. Cron job

hPanel → **Advanced → Cron Jobs** → add:

```
cd /home/uXXXX/onetimelink && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

Frequency: **every minute** (Hostinger supports it; the scheduler itself
decides what actually runs — expiry sweep hourly, pruning daily).

## 7. Production caches

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Re-run these after **every** `.env` edit or deploy — with `config:cache`
active, `.env` changes are ignored until the cache is rebuilt.

## 8. Payment webhooks

In each processor's dashboard, register:

- `https://yourdomain.com/webhooks/crypto`
- `https://yourdomain.com/webhooks/walletpay`

and paste the matching webhook secrets into `.env`
(`PAYMENT_*_WEBHOOK_SECRET`), then `php artisan config:cache` again.

## 9. Smoke test

1. `/` loads over HTTPS with the padlock and no mixed-content warnings.
2. Create a guest link → open it → redirect works → open again → *Already Redeemed*.
3. Register → verification mail arrives → dashboard shows the bonus points.
4. Log in as the seeded admin → `/admin` loads with platform stats.
5. Confirm the cron ran: hPanel cron log, or check that a link with a 0-day
   test expiry flips to `expired` within the hour.

## Updating a live site

See `docs/OPERATIONS.md` → *Upgrades* (short version: enable maintenance
mode, upload the new build, `migrate --force`, rebuild caches, disable
maintenance mode).
