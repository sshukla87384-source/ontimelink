# Operations Guide — Backups, Upgrades, Troubleshooting

## 1. Backups

Two things constitute the entire application state:

1. **The MySQL database** — users, links (encrypted destinations + token
   hashes), points, wallets, payments, referrals, audit logs, settings.
2. **`APP_KEY`** (and the rest of `.env`) — without the key, encrypted
   destinations in a database backup are unrecoverable.

`storage/app` matters only if you later add file uploads; today it holds no
user data.

### Recommended routine

- **Automatic:** Hostinger's built-in backups (hPanel → Files → Backups) run
  weekly on Premium and daily on Business — verify they include the database.
- **Manual, before every upgrade:**

  ```bash
  mysqldump -u DB_USER -p DB_NAME | gzip > backup-$(date +%F).sql.gz
  cp .env env-backup-$(date +%F)          # contains APP_KEY — store securely
  ```

- Keep at least one copy **off the hosting account** (download it). Treat
  `.env` backups like passwords.

### Restore

```bash
gunzip < backup-YYYY-MM-DD.sql.gz | mysql -u DB_USER -p DB_NAME
# restore .env (APP_KEY must be the SAME key that made the backup)
php artisan config:cache
```

## 2. Upgrades

1. Announce it (admin → Settings → site announcement), then:

   ```bash
   php artisan down --retry=60
   ```

2. Back up (section 1).
3. Build the new release locally (`composer install --no-dev
   --optimize-autoloader`) and upload it, preserving the live `.env` and
   `storage/`.
4. Apply migrations and rebuild caches:

   ```bash
   php artisan migrate --force
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   php artisan up
   ```

5. Smoke test: create + redeem a link, log in, open `/admin`.

Migrations are additive; if something goes wrong, restore the database
backup and re-upload the previous release directory.

## 3. Troubleshooting

| Symptom | Likely cause → fix |
|---|---|
| **500 on every page** | Check `storage/logs/laravel-*.log`. Most common on shared hosting: wrong paths in `public_html/index.php` (Strategy A), or `storage/` not writable → re-apply the permissions from the deployment guide. |
| **Blank page, nothing in logs** | PHP version < 8.3 selected in hPanel, or `vendor/` missing (upload included it?). |
| **`.env` changes have no effect** | Config cache is active → `php artisan config:cache` after every edit (`optimize:clear` while debugging). |
| **419 Page Expired on every form** | Session table missing (`php artisan migrate`), or `SESSION_SECURE_COOKIE=true` while browsing over plain HTTP → enforce HTTPS. |
| **Verification / reset emails never arrive** | Wrong SMTP credentials; test in hPanel → Emails. Also confirm `MAIL_FROM_ADDRESS` matches the mailbox domain (SPF). |
| **CSS/JS load but look unstyled or copy buttons dead** | `public/css/app.css` / `public/js/app.js` didn't make it into `public_html` (Strategy A copies the *contents* of `public/`). |
| **`storage:link` fails** | Symlinks restricted → create manually: `ln -s ../onetimelink/storage/app/public public_html/storage`. |
| **Links never flip to `expired`** | Cron not firing → hPanel → Cron Jobs; the command must `cd` into the project and use the full PHP path (`/usr/bin/php`). |
| **Payments stay `pending` after paying** | Webhook not registered at the processor, wrong `PAYMENT_*_WEBHOOK_SECRET`, or the secret changed without `config:cache`. A bad signature returns HTTP 403 to the processor (visible in its webhook delivery log); underpaid or failed confirmations appear in admin → Audit log as `payment.failed`. |
| **"Registration is temporarily closed"** | Admin → Settings → Registration is set to Closed. |
| **Admin locked out** | Via SSH: `php artisan tinker` → `App\Models\User::where('email','you@x.com')->first()->forceFill(['password' => 'NewTempPass!2345'])->save();` then log in and change it properly. |
| **User can't do anything / sees read-only errors** | Account status is `frozen` (or wallet frozen) — admin → Users → that user → set Active / Unfreeze. |
| **429 Too Many Requests during testing** | You hit the rate limits (they're intentional). Wait 60 seconds. |

### Reading the logs

```bash
tail -n 100 storage/logs/laravel-$(date +%F).log
```

Log rotation is daily with 14 days retention (`LOG_DAILY_DAYS`).

### Getting a clean slate in development (never in production)

```bash
php artisan migrate:fresh --seed
```

## 4. Routine health checklist (monthly)

- [ ] Backups exist and one restore has been test-driven.
- [ ] Disk usage in hPanel < 80% (logs prune themselves; DB grows with use).
- [ ] Admin → Audit log: scan `security` events for anomalies.
- [ ] SSL certificate valid (Hostinger auto-renews; verify anyway).
- [ ] PHP version still 8.3+ after any hosting-side changes.
