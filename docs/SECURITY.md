# Security Guide

This document explains the platform's security design so operators can reason
about it, audit it, and avoid weakening it accidentally.

## 1. One-time link design

**Tokens.** Each link's token is 32 bytes from `random_bytes()` (256 bits of
entropy), hex-encoded to 64 characters. The database stores only the
**SHA-256 hash** (`links.token_hash`); the raw token is shown exactly once to
the creator and never persisted. Consequence: a full database leak does not
let an attacker redeem or even enumerate valid links.

**Destinations.** Original URLs are encrypted at rest with Laravel's `Crypt`
(AES-256-CBC + HMAC) using `APP_KEY`, stored in
`links.destination_encrypted`, and hidden from all serialization.

**Atomic redemption.** Redeeming is a single guarded SQL `UPDATE ... WHERE
token_hash = ? AND status = 'active' AND (expires_at IS NULL OR expires_at >
NOW())`. Exactly one concurrent request can win (affected rows = 1); every
loser receives the *Already Redeemed* / *Expired* page. There is no
read-then-write window, so double redemption is impossible by construction —
covered by `tests/Feature/RedemptionTest`.

**Route hardening.** `/r/{token}` has a `[A-Fa-f0-9]{64}` route constraint
(malformed tokens never reach PHP logic) and a 20 req/min/IP rate limit,
making brute-force enumeration of a 256-bit space irrelevant in practice. The
redirect uses `redirect()->away()` with `Referrer-Policy: no-referrer` so the
one-time URL never leaks via the `Referer` header.

## 2. APP_KEY — read this before rotating

`APP_KEY` encrypts destination URLs, sessions, and cookies.

- **Back it up** together with the database. A database backup without the
  key cannot decrypt any destination.
- **Rotating the key makes every existing active link unredeemable** (their
  destinations can no longer be decrypted). Only rotate after a suspected key
  compromise, and expect existing links to be re-issued.

## 3. Identifiers & privacy

- All public references use **UUID v7** (`uuid` columns are the route keys);
  auto-increment IDs never appear in URLs, exports, or payloads.
- IP addresses are **never stored raw**. Everywhere an IP is recorded (guest
  quota, registration, redemption, audit log) it is first HMAC-SHA256'd with
  the app key (`AuditService::hashIp`), which supports abuse detection
  (same-IP referral fraud, quota enforcement) without holding PII.
- Passwords are bcrypt-hashed (Laravel default). Password policy: minimum 10
  characters, and in production checked against the Have-I-Been-Pwned
  compromised-password corpus (`Password::defaults()`).

## 4. HTTP-layer defenses

`ForceSecureHeaders` middleware applies to every response:

| Header | Value / effect |
|---|---|
| `Content-Security-Policy` | Self + `cdn.jsdelivr.net` (Bootstrap) only; no inline script execution beyond the audited snippets |
| `X-Frame-Options` | `DENY` — clickjacking protection |
| `X-Content-Type-Options` | `nosniff` |
| `Referrer-Policy` | `strict-origin-when-cross-origin` (and `no-referrer` on redemption redirects) |
| `Permissions-Policy` | Camera/mic/geolocation disabled |
| `Strict-Transport-Security` | Sent when the request is secure (HSTS) |

Cookies are `Secure`, `HttpOnly`, `SameSite=Lax`, and the session payload is
encrypted (`SESSION_ENCRYPT=true`).

## 5. Application-layer defenses

- **CSRF** — Laravel's token middleware on all state-changing routes. The
  only exemption is `/webhooks/{gateway}`, which substitutes HMAC signature
  verification (below).
- **SQL injection** — exclusively Eloquent/query-builder parameter binding;
  no raw user input in queries.
- **XSS** — Blade escaping everywhere; no `{!! !!}` output of user content.
- **Authorization** — policies and gates (`LinkPolicy`, `admin` gate) on
  every owner- or role-scoped action; admin routes additionally sit behind
  the `admin` middleware.
- **Session fixation** — the session ID is regenerated on login,
  registration, and password change; password change also logs out all other
  devices.
- **Rate limiting** — redemption 20/min/IP, link generation 15/min, auth
  endpoints 6/min keyed by email+IP (login throttling), webhooks 60/min.
- **Account states** — banned users are force-logged-out on the next
  request; frozen users are read-only (`EnsureAccountIsActive`).
- **Email verification** — signed, throttled verification URLs; referral
  rewards are paid **only after** verification, so disposable fake signups
  earn nothing.

## 6. Money & points integrity

- Points and wallet movements run inside database transactions with
  `lockForUpdate()` on the owning row; balances can never go negative
  (guarded in `PointService`/`WalletService` and enforced by tests).
- Every movement is ledgered with a running `balance_after`, actor
  (`performed_by`), and a polymorphic reference to its cause.
- Wallet entries carry a **unique `reference_id`** idempotency key — a
  replayed webhook or double-submitted form cannot credit twice.
- Payment webhooks: raw-body HMAC-SHA256 verified with `hash_equals`
  (constant-time), then the confirmation is an idempotent, row-locked status
  transition. Amount fields from the webhook are checked against the stored
  payment before anything is credited.

## 7. Audit trail

`AuditService` writes append-only rows (no `updated_at`) for security events
(logins, failures, status changes), user activity (link created/redeemed,
payments), and every admin action (status changes, point/wallet adjustments,
setting edits) — each with actor, hashed IP, and JSON context, all browsable
and filterable in the admin panel.

## 8. Operational hygiene

- `APP_DEBUG=false` in production — debug pages disclose secrets.
- Keep `.env` outside the web root (deployment Strategy A) and `chmod 600`.
- The bundled `.htaccess` files deny dotfiles and refuse to serve
  `.env`/`composer.*` even in the fallback layout.
- `robots.txt` disallows `/r/` so redemption URLs are never crawled or
  cached by search engines.
- Review the admin **Audit log** periodically; `security`-category events
  with repeated login failures for one account indicate credential stuffing
  (the per-email throttle already slows it to 6/min).

## 9. Reporting

If you discover a vulnerability, do not open a public issue — contact the
site operator privately and include reproduction steps.
