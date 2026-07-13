# One-Time Link

A production-ready SaaS platform for **one-time redemption links**, built with
Laravel 12, PHP 8.3+, MySQL, Blade, and Bootstrap 5 — optimised to run on
Hostinger Premium/Business **shared hosting** (no VPS, Docker, Redis, queue
workers, or Node runtime required).

Submit a URL → the platform encrypts it and issues a cryptographically secure
link → the **first** visitor is redirected to the destination → every later
visitor permanently sees *Already Redeemed*. The destination is sealed forever
after first use.

## Feature summary

| Area | What you get |
|---|---|
| One-time links | AES-256-encrypted destinations, 256-bit tokens (only a SHA-256 hash is stored), atomic single-winner redemption, optional expiry, disable, labels |
| Guests | One free link per visitor, then registration required |
| Points | 1 point = 1 link, full double-entry-style ledger (earn / spend / refund / bonus / admin), balances can never go negative |
| Bulk generation | Multi-URL textarea + CSV import, duplicate detection, per-row error report, transaction-safe |
| Referrals | Unique referral links, +10 pts referrer / +20 pts new user, rewarded only after email verification, self-referral & same-IP abuse blocked, idempotent payout |
| Wallet | Per-user balance in integer minor units, deposits, admin adjustment, freeze/unfreeze, idempotent ledger with reference IDs |
| Payments | Cryptocurrency & WalletPay only, pluggable `PaymentGateway` contract, HMAC-verified idempotent webhooks |
| Dashboards | Full user dashboard (stats, ledgers, referral earnings, activity timeline, profile) and admin panel (users, links, referrals, payments, audit/security logs, settings, CSV exports, search/filter/pagination) |
| Security | CSP, HSTS, clickjacking & MIME protections, rate limiting everywhere, login throttling, hashed IPs, UUID-only public identifiers, policies/gates, session regeneration, signed verification URLs, audit logging |
| Shared hosting | Database sessions/cache/queue, single standard cron entry, route/config/view caching, `.htaccess` strategies for `public_html` |

## Documentation

- [`docs/INSTALL.md`](docs/INSTALL.md) — requirements, environment variables, database setup, migrations, seeding, local run
- [`docs/DEPLOYMENT_HOSTINGER.md`](docs/DEPLOYMENT_HOSTINGER.md) — step-by-step shared-hosting deployment, storage link, permissions, cron
- [`docs/SECURITY.md`](docs/SECURITY.md) — threat model, token design, key management, webhook signatures
- [`docs/OPERATIONS.md`](docs/OPERATIONS.md) — backups, upgrades, troubleshooting

## Quick start (local)

```bash
composer install
cp .env.example .env
php artisan key:generate
# configure DB_* in .env, then:
php artisan migrate --seed        # set ADMIN_EMAIL in .env first
php artisan storage:link
php artisan serve
```

Run the test suite:

```bash
php artisan test
```

## License

Proprietary — all rights reserved by the project owner.
