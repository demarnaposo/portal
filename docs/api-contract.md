# Kontrak API Portal v1.0 (portal Laravel → landing Astro)

Endpoint publik milik **portal** (`http://localhost:8001` lokal). Katalog tetap di API CMS
(kontrak CMS `statamic/docs/api-contract.md` v1.4). Perubahan kontrak harus diperbarui di repo portal dan
salinannya di Astro (`astro/docs/portal-api-contract.md`).

## Konvensi
- Base URL lokal: `http://localhost:8001/api`.
- CORS hanya untuk origin di env `CORS_ALLOWED_ORIGINS` (lokal: `http://localhost:4321,http://localhost:4323`),
  `Access-Control-Allow-Credentials: true`.
- Semua respons `Cache-Control: no-store, private`.
- Error 422: format validasi Laravel (`{ "message", "errors": { field: [..] } }`). Error lain: `{ "message": "..." }`.

## Tipe

```ts
// Sama persis dengan `AssessmentResult` kontrak CMS v1.3 (Category & ProgramSummary dari kontrak CMS).
type AssessmentResult = {
  version: number;
  scores: { category: Category; score: number }[];   // urut skor tertinggi
  recommended_category: Category;
  recommendations: ProgramSummary[];                  // maks 3
  attempt_token: string;  // token acak sekali-tampil (48 karakter alfanumerik); portal hanya menyimpan sha256-nya
};

type Session = { authenticated: false } | { authenticated: true; name: string };
```

## Endpoint

| Method | Path | Respons | Catatan |
|---|---|---|---|
| POST | `/assessment/score` body `{ "answers": { "<question_id>": "<option_id>" } }` | `{ data: AssessmentResult }` | Tanpa cookie. Rate limit 30/menit/IP (`ASSESSMENT_SCORE_RATE_LIMIT`). |
| GET | `/session` | `Session` (tanpa pembungkus `data`) | Browser wajib `fetch(url, { credentials: 'include' })`. |

### `POST /assessment/score`
1. Portal memvalidasi bentuk (`answers` objek, maks 100 entri, key `[A-Za-z0-9_-]`, nilai string ≤ 100) → 422.
2. Portal memanggil CMS `POST /api/v1/internal/assessment/score` (server-to-server, `Authorization: Bearer
   PORTAL_SERVICE_TOKEN`). CMS memvalidasi isi & menghitung skor dengan bobot yang tidak pernah keluar dari CMS.
3. 422/404 dari CMS diteruskan apa adanya (key error `answers.<question_id>` / `answers`). CMS tidak terjangkau,
   timeout, atau error lain → **503** `{ "message": "Layanan assessment sedang tidak tersedia. …" }`.
4. Sukses → attempt anonim disimpan di DB portal (`assessment_attempts`, token di-hash) dan respons berisi
   `attempt_token`.

### Klaim hasil ke akun (bukan API JSON)
- Tombol "Simpan hasil & daftar" di landing → `GET {PUBLIC_APP_URL}/assessment/claim?token=<attempt_token>`.
  Belum login: token disimpan di session lalu diarahkan ke `/register`; sudah login: langsung diklaim → `/dashboard`.
- Alternatif: `?assessment=<attempt_token>` pada URL portal mana pun (mis. `/register?assessment=…`, `/login?assessment=…`).
- Klaim atomik dan hanya sekali; token tak dikenal/sudah diklaim diabaikan tanpa error.
- Attempt anonim yang tidak diklaim dihapus setelah `ASSESSMENT_ANON_RETENTION_DAYS` (default 30) hari.
- Token jangan dicatat ke analytics/log; simpan paling lama selama sesi browser.

### `GET /session`
- Untuk header landing: `Halo, <name> · Dashboard` bila `authenticated`, selain itu `Masuk`.
- Sengaja hanya `authenticated` & `name` (tanpa email/telepon/NPWP). Admin yang login di `/admin` **bukan**
  customer (`authenticated: false`).
- Gagal (portal mati, CORS, offline) → landing menampilkan `Masuk`.
- Cookie session portal ber-`SameSite=Lax`: terkirim karena landing & portal satu *site*
  (lokal `localhost:4321` ↔ `localhost:8001`; produksi mis. `sertifika.id` ↔ `app.sertifika.id`).

## URL halaman portal yang dipakai landing (`PUBLIC_APP_URL`)

| Path | Isi |
|---|---|
| `/register`, `/login` | Daftar / masuk customer |
| `/dashboard` | Dashboard customer |
| `/assessment/claim?token=` | Klaim hasil assessment |
| `/checkout?batch=<id>&qty=<n>` | Ringkasan batch (harga dari CMS `/batches/{id}`); checkout penuh menyusul (modul Commerce) |

## Changelog
- **v1.0** (2026-10-27): rilis awal — `POST /assessment/score` (pindah dari CMS `POST /api/v1/assessment/score`,
  bentuk respons sama dengan CMS v1.3) dan `GET /session`.
