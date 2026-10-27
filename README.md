# Portal Sertifika (customer + panel admin)

Aplikasi Laravel terpisah dari CMS: akun customer (peserta), dashboard, hasil assessment, ringkasan checkout,
dan panel admin internal. Konten & harga tetap milik CMS Statamic (`../statamic`), landing publik di Astro
(`../../astro`).

```
landing Astro :4321 ──POST /api/assessment/score──▶ portal :8001 ──Bearer──▶ CMS :8000 /api/v1/internal/assessment/score
        │        ──GET /api/session (cookie)──────▶ portal                    (scoring & bobot tetap di CMS)
        └─ tautan /register, /assessment/claim, /checkout ─▶ portal ──GET /programs, /batches/{id}, /site──▶ CMS
```

## Stack (terpasang 2026-10-27)

|             | Versi                                                                                                          |
| ----------- | -------------------------------------------------------------------------------------------------------------- |
| PHP         | 8.4 (Homebrew `php@8.4`; CMS memakai 8.3+, `composer.json` portal `^8.3`)                                      |
| Laravel     | 13.38 (rilis stabil terbaru; Laravel tidak memakai label LTS)                                                  |
| Titik awal  | starter kit resmi `laravel/vue-starter-kit` (fitur: 2FA + konfirmasi sandi; tanpa registrasi/passkeys Fortify) |
| Inertia     | `inertiajs/inertia-laravel` 3.5 + `@inertiajs/vue3` 3.9                                                        |
| Frontend    | Vue 3.5 + TypeScript 5.9 (`<script setup lang="ts">`), Tailwind CSS 4.3, shadcn-vue (reka-ui 2.11), Wayfinder  |
| Auth admin  | Laravel Fortify 1.41 (login, tantangan 2FA TOTP, konfirmasi sandi)                                             |
| Test        | PHPUnit 12                                                                                                     |
| Dev tooling | Laravel Boost 2.10 (guidelines + skills + MCP), Vite+ (`vp`: build, lint, format)                              |

Package manager JS: **Bun** (`bun install`, `bun run build|dev|check|types:check`), sama seperti landing.
Vite+ butuh Node di PATH (mis. `export PATH="$HOME/.nvm/versions/node/v24.16.0/bin:$PATH"`).

## Struktur

```
modules/                                namespace Modules\ — modul lain hanya lewat Contracts/Events
├── Identity/      customer: register (persetujuan Kebijakan Privasi wajib), login (throttle), logout,
│                  verifikasi email & reset sandi (Bahasa Indonesia, lewat queue), profil + perusahaan/NPWP (terenkripsi)
│                  Contracts: CustomerDirectory, CustomerData (NPWP hanya tersamar), CustomerRegistered/CustomerAuthenticated
├── Assessment/    POST /api/assessment/score (CORS, rate limit) → scoring CMS → attempt (token di-hash) + attempt_token;
│                  klaim /assessment/claim?token= & ?assessment= (sekali, atomik); model:prune harian (anonim > 30 hari)
│                  Contracts: AssessmentResults
├── Cms/           klien HTTP server-to-server ke CMS_API_URL (timeout, error jelas); /site di-cache 10 menit,
│                  /programs & /batches/{id} tidak di-cache (harga selalu dari CMS). Contracts: CmsApi, CmsRequestFailed
├── Portal/        UI customer: /dashboard (hasil terbaru + 3 rekomendasi /programs), /assessments, /checkout?batch=&qty=
│                  (ringkasan batch dari CMS, "Checkout segera hadir"), /orders & /programs (segera hadir), GET /api/session
└── Admin/         /admin: guard `admin`, tabel `admins`, Fortify + 2FA wajib (EnsureTwoFactorEnabled), role owner/operator
                   (Gate viewCustomers, viewAdmins), `php artisan admin:create`; dashboard, customer (NPWP tersamar),
                   hasil assessment, daftar admin (owner); menu Pesanan/Peserta/Kuota batch/Sinkron Odoo "segera hadir"
app/Http/Middleware/HandleInertiaRequests.php   props bersama per area (customer: nama/email + data situs CMS; admin: akun admin)
resources/css/theme.css                         token desain — salinan identik astro/src/styles/theme.css (sumber kebenaran: Astro)
resources/js/pages/portal/**                    halaman customer (layout header/footer meniru landing)
resources/js/pages/admin/**                     halaman admin (layout sidebar starter kit, shadcn-vue)
docs/api-contract.md                            kontrak endpoint publik portal (/api/assessment/score, /api/session)
```

Batas modul diuji `tests/Unit/ModuleBoundariesTest.php`. Nanti: **Commerce** (checkout, pembayaran, pesanan) dan
**Learner** (enrollment, dokumen, sertifikat) sebagai modul baru dengan pola yang sama.

## Guard & keamanan

|               | Customer                                                                 | Admin                                                         |
| ------------- | ------------------------------------------------------------------------ | ------------------------------------------------------------- |
| Guard / tabel | `customer` / `customers` (ULID)                                          | `admin` / `admins`                                            |
| Area          | `/` (`/login`, `/register`, `/dashboard`, …)                             | `/admin`                                                      |
| Login         | `LoginRequest`: kunci 60 dtk setelah 5 gagal per email+IP, + 20/menit/IP | Fortify: 5/menit per email+IP; 2FA 5/menit                    |
| 2FA           | —                                                                        | TOTP wajib; tanpa 2FA hanya halaman Keamanan yang bisa dibuka |

- Customer tidak bisa membuka `/admin` (dialihkan ke login admin); admin bukan customer (`/api/session` →
  `authenticated: false`). Satu cookie session (`sertifika-portal-session`) dipakai kedua guard; logout di salah satu
  area meng-invalidate session sehingga area lain ikut keluar.
- Cookie browser tidak dipisah per port: nama cookie session portal berbeda dari CMS (`statamic-session`). CSRF
  Laravel 13 lolos lewat `Sec-Fetch-Site: same-origin`, jadi cookie `XSRF-TOKEN` bersama di `localhost` tidak bentrok.
- NPWP terenkripsi (`APP_KEY`; jangan ganti tanpa re-enkripsi), hanya dikirim di halaman Profil customer; panel
  admin hanya menampilkan 4 digit terakhir.
- `url.intended` lintas area diabaikan (customer tidak dilempar ke /admin, admin tidak ke halaman customer).
- Portal tidak mengakses tabel CMS: data katalog hanya lewat API CMS.

## Menjalankan lokal

Semua layanan (CMS, portal, Astro, MariaDB, Mailpit) dijalankan dari repo CMS: `../statamic/deploy/dev-stack.sh up`
(lihat `../statamic/docs/local-dev.md`). Manual:

```bash
composer install && bun install
cp .env.example .env && php artisan key:generate   # isi DB_PASSWORD & PORTAL_SERVICE_TOKEN
php artisan migrate
bun run build                                      # atau: bun run dev (HMR)
php artisan serve --port=8001                      # http://localhost:8001
php artisan queue:work --tries=5                   # email verifikasi/reset → Mailpit http://localhost:8025
php artisan schedule:work                          # model:prune harian
php artisan admin:create pemilik@sertifika.id --name="Pemilik"   # tanpa TTY (-n): sandi acak ditampilkan sekali
```

Database: `portal` dengan user `portal` di container `mariadb-dev` (port 3307), terpisah dari database `statamic`.

## Environment

| Variabel                         | Default                                       | Keterangan                                                                             |
| -------------------------------- | --------------------------------------------- | -------------------------------------------------------------------------------------- |
| `APP_URL`                        | `http://localhost:8001`                       | URL portal (tautan email).                                                             |
| `DB_*`                           | `mariadb` 127.0.0.1:3307 `portal`             | DB sendiri.                                                                            |
| `SESSION_COOKIE`                 | `sertifika-portal-session`                    | Beda nama dari cookie CMS.                                                             |
| `LANDING_URL`                    | `http://localhost:4321`                       | Menu header/footer, halaman program, kebijakan privasi.                                |
| `CORS_ALLOWED_ORIGINS`           | `http://localhost:4321,http://localhost:4323` | Origin landing untuk `/api/*` (dengan credentials).                                    |
| `CMS_API_URL`                    | `http://localhost:8000/api/v1`                | API CMS (server-to-server).                                                            |
| `CMS_API_TIMEOUT`                | `10`                                          | Detik per request ke CMS.                                                              |
| `PORTAL_SERVICE_TOKEN`           | —                                             | Bearer untuk scoring internal CMS; **sama** dengan `.env` CMS. `openssl rand -hex 32`. |
| `ASSESSMENT_ANON_RETENTION_DAYS` | `30`                                          | Attempt anonim yang tak diklaim dihapus setelah N hari.                                |
| `ASSESSMENT_SCORE_RATE_LIMIT`    | `30`                                          | `POST /api/assessment/score` per IP per menit.                                         |
| `MAIL_*`                         | Mailpit 127.0.0.1:1025                        | Email transaksional (queue `database`).                                                |

## Test & kualitas

```bash
php artisan test            # PHPUnit (Http CMS di-fake; tidak ada request keluar)
vendor/bin/pint --test
vendor/bin/phpstan analyse  # app/ (modul belum masuk cakupan)
bun run types:check         # vue-tsc
bun run check               # Vite+ lint + format
```

## Skills AI (`.claude/skills/`)

- Laravel Boost (`php artisan boost:install`): `laravel-best-practices`, `fortify-development`, `wayfinder-development`,
  `tailwindcss-development`, `testing-best-practices`, `infer-conventions`, `deploying-to-cloud` + guidelines di
  `AGENTS.md` (dimuat lewat `CLAUDE.md`).
- `vue-best-practices` dari [vuejs-ai/skills](https://github.com/vuejs-ai/skills) (komunitas dengan keterlibatan tim
  inti Vue, bukan rilis resmi Vue; markdown saja, commit `198f0cd`).
- `ui-ux-pro-max`, `design-system` disalin dari repo Astro.

## Produksi (catatan)

- Portal di subdomain sendiri (mis. `app.sertifika.id`), `SESSION_SECURE_COOKIE=true`, `SESSION_DOMAIN` tidak di-share.
  Landing & portal harus satu _site_ (registrable domain sama) agar cookie `SameSite=Lax` terkirim ke `/api/session`.
- Worker `queue:work` dan scheduler wajib jalan. Mailer transaksional (SES/Postmark/dll.).
- `/admin` sebaiknya dibatasi tambahan di level jaringan (VPN/IP allowlist) bila memungkinkan.
