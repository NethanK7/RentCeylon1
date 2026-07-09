# RentCeylon 🔑

**Rent anything and everything in Sri Lanka.** A peer-to-peer rental marketplace
(Airbnb-style) plus a property-management service for Sri Lankans abroad.

Built from the *RentLoop Platform Spec v2* — renamed **RentCeylon**.

- **`backend/`** — Laravel 13 app: merged **web frontend (Inertia + React + TypeScript + Tailwind)** *and* **REST API (Sanctum)** in one codebase.
- **`mobile/`** — Flutter app (iOS + Android) consuming the same Sanctum API.

---

## What works today (runnable & verified)

| Area | Status |
| --- | --- |
| Full DB schema — all 28-page data model (listings, categories+feature-flags, bookings, escrow deposits, disputes, reviews, messaging, referrals, property mgmt, immutable audit + SLA tracking) | ✅ |
| Eloquent models + enums + booking **state machine** | ✅ |
| RBAC (renter / lister / admin / manager) + Sanctum token API | ✅ |
| Signup with **role selection + non-pre-checked ToS gate** + referral attribution | ✅ |
| Categories with **day-one feature flags** + **typed, inherited filters** (Vehicles → type/transmission/fuel/seats…) | ✅ |
| Airbnb-style **Home / Browse+filters / Listing detail** | ✅ |
| **Checkout**: tiered platform fee (10/7/5%), deposit → **escrow**, **idempotent** payment, policy + agreement acceptance, contact revealed only after payment | ✅ |
| Booking confirmation with condition-photo gate indicators | ✅ |
| Pricing / Trust / Property-Management landing pages | ✅ |
| Role dashboards (renter/lister/admin/manager) — shells | ✅ |
| Flutter app: login (token), browse + categories, listing detail with badge zones | ✅ |

### Global constraints already enforced in code
- **Badge separation** (earned vs paid) — distinct components & zones everywhere (`Badges.tsx`, `BadgeClass` enum).
- **Idempotency on payments** — unique key; verified double-fire = one charge.
- **Deposit release is manual** — escrow ledger, no auto-release path except SLA default.
- **Contact gated behind payment** — `phone_revealed` flips only on confirmed payment.
- **Tiered fee** — `App\Support\PlatformFee`.
- **Category feature flags** — `categories.is_enabled`, no rebuild to toggle.

### Still to build (iterative — schema & models already in place)
Return flow + cancellation logic, rental history, lister create/edit-listing & incoming-bookings & subscription screens, disputes UI, blind review UI, monitored messaging UI, admin action queues, property-management dashboards, background jobs for SLAs & PDF generation. The **data layer for all of these already exists**, so they are UI + controller work on top of the current foundation.

---

## Run the backend (web + API)

```bash
cd backend
composer install
npm install
cp .env.example .env        # (already present in dev)
php artisan key:generate
php artisan migrate:fresh --seed
npm run build               # or: npm run dev  (hot reload)
php artisan serve           # http://localhost:8000
```

Local dev uses **SQLite** out of the box (zero config). For MySQL, see below.

### Demo accounts (password: `password`)
| Role | Email |
| --- | --- |
| Admin | `admin@rentceylon.lk` |
| Property Manager | `manager@rentceylon.lk` |
| Lister | `ravi@example.com`, `auto@example.com`, `events@example.com` |
| Renter | `amaya@example.com`, `kasun@example.com`, `dilani@example.com` |

---

## Run the mobile app

```bash
cd mobile
flutter pub get
# Android emulator reaches your machine on 10.0.2.2 (default in lib/config.dart)
flutter run
# Point at another API host:
flutter run --dart-define=API_BASE=https://rentceylon.lk/api
```

Log in with a demo account (pre-filled: `amaya@example.com` / `password`).

---

## Switch to MySQL / MariaDB (for hosting)

1. Create the database & user:
   ```sql
   CREATE DATABASE rentceylon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'rentceylon'@'localhost' IDENTIFIED BY 'a-strong-password';
   GRANT ALL PRIVILEGES ON rentceylon.* TO 'rentceylon'@'localhost';
   ```
2. Copy `backend/.env.production.example` → `.env`, fill DB + keys + payment/SMS/storage creds.
3. `php artisan key:generate && php artisan migrate --force && php artisan db:seed --class=CategorySeeder --force`
4. `npm ci && npm run build`

The migrations are portable — no code changes needed to move SQLite → MySQL.

---

## Production deployment checklist

- **Web server**: Nginx/Apache → `backend/public`. PHP 8.4-FPM.
- **Build assets**: `npm run build` (outputs to `public/build`).
- **Storage**: set `FILESYSTEM_DISK=s3` (AWS S3 or Cloudflare R2). Run `php artisan storage:link` if using local disk. Condition photos & ID docs use signed URLs.
- **Queue worker**: `php artisan queue:work` (Supervisor) — for photo-upload retries, PDFs, notifications.
- **Scheduler** (SLA clocks, deposit SLA defaults, review prompts): add cron
  `* * * * * cd /path/to/backend && php artisan schedule:run >> /dev/null 2>&1`
- **Payments**: fill PayHere + iPay creds (Stripe optional fallback). Wire gateway callbacks to confirm `payments` (the service currently simulates capture).
- **SMS/OTP**: Dialog Axiata or Mobitel creds.
- **HTTPS + `SANCTUM_STATEFUL_DOMAINS`** set to your domain(s).
- `APP_DEBUG=false`, `APP_ENV=production`.

---

## Architecture notes

- **One Laravel app serves both** the Inertia/React website (session auth) and the JSON API (`/api/*`, Sanctum tokens for Flutter).
- **Typed filter system**: `category_attributes` defines filterable attributes per category; children inherit ancestors' attributes (`Category::resolvedAttributes()`), so adding a category or filter needs no schema change.
- **Booking state machine**: `App\Enums\BookingStatus` with explicit allowed transitions.
- **SLA & audit**: `sla_events` (never-dropped clocks) and immutable `audit_logs`.
```
backend/app/
  Enums/         Role, BookingStatus, DepositStatus, ListingStatus, VerificationStatus, BadgeClass
  Models/        38 models covering every spec entity
  Services/      BookingService (fees, escrow, idempotency)
  Support/       PlatformFee (tiered fee)
  Http/Controllers + Api/   web + mobile controllers
resources/js/    React pages, SiteLayout, badge/card/icon components
```
