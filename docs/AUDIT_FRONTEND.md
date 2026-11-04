# AUDIT FRONTEND — Sistem Kerjasama BBLSDM Komdigi

Dokumen ini hasil analisis **read-only** seluruh repo pada 4 November 2026.
Tidak ada file lain yang diubah. Tidak ada perintah `migrate`, `seed`, `install`, atau `build` yang dijalankan.
Isi `.env` tidak dibaca; daftar variabel diambil dari `.env.example`.

Catatan penting yang berlaku untuk hampir semua bagian:

> **UI yang benar-benar hidup adalah Blade + CSS statis di `public/assets/`.**
> Stack React/Inertia tetap terpasang, tetapi route Inertia **tidak terdaftar** (lihat §3 dan §4),
> sehingga `resources/js/pages/*` tidak dapat diakses lewat HTTP.

---

## 1. STACK DAN VERSI

### 1.1 Backend

| Komponen | Constraint (`composer.json`) | Versi terpasang (`composer.lock`) |
|---|---|---|
| PHP | `^8.3` (`composer.json:12`) | 8.4.26 (CLI di mesin ini) |
| Laravel Framework | `^13.17` (`composer.json:16`) | v13.35.0 |
| Inertia server | `^3.0` (`composer.json:13`) | v3.5.1 |
| Laravel Fortify | `^1.37.2` (`composer.json:15`) | v1.41.0 |
| Laravel Wayfinder | `^0.1.14` (`composer.json:18`) | v0.1.21 |
| Laravel Chisel | `^0.1.0` (`composer.json:14`) | v0.1.1 |
| Laravel Tinker | `^3.0` (`composer.json:17`) | v3.0.2 |

Nama paket composer masih `laravel/react-starter-kit` (`composer.json:3`).

### 1.2 Frontend

| Komponen | Constraint (`package.json`) | Versi terpasang (`package-lock.json`) |
|---|---|---|
| Inertia client (`@inertiajs/react`) | `^3.0.0` (`package.json:14`) | 3.9.1 |
| Inertia Vite plugin | `^3.0.0` (`package.json:15`) | 3.9.1 |
| React / React DOM | `^19.2.0` (`package.json:38-39`) | 19.3.0 |
| TypeScript | `^5.7.2` (`package.json:44`) | 5.9.3 |
| Vite | `^8.0.0` (`package.json:45`) | 8.3.4 |
| `vite-plus` (runner `vp`) | `0.3.0` (`package.json:52`) | 0.3.0 |
| Tailwind CSS | `^4.0.0` (`package.json:42`) | 4.3.3 |
| `@tailwindcss/vite` | `^4.1.11` (`package.json:29`) | 4.3.3 |
| laravel-vite-plugin | `^3.0.0` (`package.json:36`) | 3.2.0 |
| `@vitejs/plugin-react` | `^6.1.1` (`package.json:32`) | 6.1.2 |
| `@laravel/vite-plugin-wayfinder` | `^0.1.3` (`package.json:48`) | 0.1.10 |

**shadcn/ui:** tidak ada sebagai dependency; hanya konfigurasi generator di `components.json`
(style `new-york`, `baseColor: neutral`, `iconLibrary: lucide`, alias `@/components`, `@/components/ui`, `@/lib`, `@/hooks`).
Komponen hasil generate ada di `resources/js/components/ui/` (23 file).

**Library UI lain:** Radix UI (avatar, checkbox, collapsible, dialog, dropdown-menu, label, navigation-menu,
select, separator, slot, toggle, toggle-group, tooltip), `lucide-react@0.475.0`, `sonner@2.0.8`,
`class-variance-authority@^0.7.1`, `clsx@^2.1.1`, `tailwind-merge@^3.0.1`, `tw-animate-css@^1.4.0`,
`babel-plugin-react-compiler@^1.0.0`, `concurrently@^10.0.3`.
Tailwind dikonfigurasi CSS-first di `resources/css/app.css:1-8` (tanpa `tailwind.config.js`).

**Yang dipakai UI aktif:** CSS statis `public/assets/{shared,admin,user}/css/` + Blade. Tidak ada Tailwind
di halaman yang tayang (sesuai `AGENTS.md`).

### 1.3 Package manager

- **npm** — bukti: `package-lock.json` (277 KB, lockfileVersion 3), `.npmrc` berisi `ignore-scripts=true`,
  dan script composer memanggil `npm install` / `npm run build` / `npx concurrently` (`composer.json:46-57`).
- `pnpm-workspace.yaml` juga ada (`packages: ['.']`, `publicHoistPattern: @inertiajs/core`) tetapi **tidak ada `pnpm-lock.yaml`**.
- Yarn: **TIDAK ADA** (`yarn.lock` tidak ditemukan).

**Script `package.json`** (`package.json:5-12`):

| Script | Perintah |
|---|---|
| `build` | `vp build` |
| `build:ssr` | `vp build && vp build --ssr` |
| `dev` | `vp dev` |
| `check` | `vp check` (lint + format check, konfigurasi di `vite.config.ts:42-76`) |
| `check:fix` | `vp check --fix` |
| `types:check` | `tsc --noEmit` |

**Script `composer.json`** (`composer.json:45-98`): `setup`, `dev`, `lint`, `lint:check`, `ci:check`,
`types:check`, `test`, plus hook `post-autoload-dump`, `post-update-cmd`, dll.

### 1.4 Tool kualitas

| Tool | Status | Bukti / perintah |
|---|---|---|
| Pint | **ADA** | `pint.json` (`preset: laravel`); `composer lint` = `pint --parallel`, `composer lint:check` = `pint --parallel --test` |
| PHPStan (Larastan) | **ADA** | `phpstan.neon` (level 7, path `app/ bootstrap/app.php config/ database/ routes/`); `composer types:check` = `phpstan analyse` |
| Pest | **ADA** (v5.3.2) | `tests/Pest.php`; `composer test` → `php artisan test`; `phpunit.xml` dua suite (Unit, Feature) |
| PHPUnit | ADA via Pest | `phpunit.xml` |
| ESLint | **TIDAK ADA** sebagai dependency | Tidak ada `.eslintrc*`/`eslint.config.*`; lint JS memakai `vp check` (vite-plus) |
| Prettier | **TIDAK ADA** sebagai dependency | Tidak ada `.prettierrc*`; format memakai `vp check --fix` dengan blok `fmt` di `vite.config.ts:59-76` (printWidth 80, tabWidth 4, singleQuote, semi) |

Catatan: `vite.config.ts:49-52` mengecualikan `resources/js/actions/**`, `resources/js/routes/**`,
`resources/js/wayfinder/**`, `resources/js/components/ui/*` dari lint/format (kode hasil generate).

---

## 2. POHON STRUKTUR FOLDER

```
app/
├─ Actions/Fortify/ResetUserPassword.php
├─ Concerns/{PasswordValidationRules.php, ProfileValidationRules.php}
├─ Console/Commands/            (kosong)
├─ Http/
│  ├─ Controllers/Controller.php
│  ├─ Controllers/Settings/{ProfileController.php, SecurityController.php}
│  ├─ Middleware/{AdminOnly.php, HandleAppearance.php, HandleInertiaRequests.php, HideAdmin.php}
│  └─ Requests/Settings/{PasswordUpdateRequest.php, ProfileDeleteRequest.php, ProfileUpdateRequest.php, TwoFactorAuthenticationRequest.php}
├─ Models/User.php
└─ Providers/{AppServiceProvider.php, FortifyServiceProvider.php}

routes/
├─ admin.php
├─ console.php
├─ settings.php          (TIDAK terdaftar — lihat §3)
├─ web.bawaan.php        (TIDAK terdaftar — lihat §3)
└─ web.php

resources/
├─ css/app.css
├─ js/
│  ├─ actions/           (hasil generate Wayfinder: Illuminate, Inertia, Laravel/Fortify)
│  ├─ components/
│  │  ├─ ui/             (23 komponen shadcn)
│  │  └─ *.tsx           (20 komponen aplikasi)
│  ├─ hooks/             (7 hook)
│  ├─ layouts/{app,auth,settings}/ + app-layout.tsx, auth-layout.tsx
│  ├─ lib/utils.ts
│  ├─ pages/{dashboard,welcome}.tsx, auth/, settings/
│  ├─ routes/            (hasil generate Wayfinder)
│  ├─ types/             (auth, navigation, ui, global.d.ts, vite-env.d.ts)
│  ├─ wayfinder/index.ts
│  └─ app.tsx
└─ views/
   ├─ app.blade.php                     (root Inertia)
   ├─ landing.blade.php, login.blade.php, pengajuan.blade.php
   ├─ admin/{artikel,galeri,inbox,login,mitra_draf,tentang,_cms}.blade.php
   ├─ components/asset.blade.php
   ├─ layouts/{admin,user,user-public}.blade.php
   ├─ partials/{header,footer-komdigi}.blade.php
   └─ user/{draf,profile,status}.blade.php

database/
├─ factories/UserFactory.php
├─ migrations/{0001_01_01_000000_create_users_table.php, ..._000001_create_cache_table.php, ..._000002_create_jobs_table.php}
├─ seeders/DatabaseSeeder.php
└─ database.sqlite

config/  (13 file)
├─ admin_demo.php, app.php, auth.php, cache.php, database.php, filesystems.php,
   fortify.php, inertia.php, logging.php, mail.php, queue.php, services.php, session.php

tests/
├─ Pest.php, TestCase.php
├─ Feature/{DashboardTest.php, ExampleTest.php, Auth/{AuthenticationTest.php, PasswordResetTest.php}, Settings/{ProfileUpdateTest.php, SecurityTest.php}}
└─ Unit/ExampleTest.php
```

Di luar cakupan tree di atas (tidak diaudit): `vendor/`, `node_modules/`, `public/build/`,
`resources/js/actions/`, `resources/js/routes/`, `resources/js/wayfinder/` (kode generate).

---

## 3. DAFTAR ROUTE

Sumber: `php artisan route:list --json` (read-only) + pembacaan `routes/*.php`.
Semua route di `routes/web.php` dan `routes/admin.php` memakai middleware group `web`.

### 3.1 `routes/web.php` (terdaftar)

| Method | URL | Nama | Aksi | Middleware | Lokasi |
|---|---|---|---|---|---|
| GET/HEAD | `/` | `landing` | `ViewController` → view `landing` | web | `routes/web.php:22` |
| GET/HEAD | `/pengajuan` | `pengajuan.create` | `ViewController` → view `pengajuan` | web | `routes/web.php:23` |
| POST | `/pengajuan` | `pengajuan.store` | Closure (validasi inline + tulis JSON) | web | `routes/web.php:24-98` |
| GET/HEAD | `/login` | `login` | `ViewController` → view `login` | web | `routes/web.php:101` |
| POST | `/login` | `login.post` | Closure (redirect, tanpa autentikasi) | web | `routes/web.php:102` |
| POST | `/logout` | `logout` | Closure (hapus `mitra_data`) | web | `routes/web.php:103-106` |
| GET/HEAD | `/mitra` | `mitra.status` | Closure → view `user.status` | web | `routes/web.php:109-112` |
| GET/HEAD | `/mitra/profile` | `mitra.profile` | Closure → view `user.profile` | web | `routes/web.php:114-117` |
| POST | `/mitra/profile` | `mitra.profile.update` | Closure (validasi inline + session) | web | `routes/web.php:119-153` |
| GET/HEAD | `/mitra/draf` | `mitra.draf` | Closure → view `user.draf` | web | `routes/web.php:155-158` |
| GET/HEAD | `/api/pddikti` | (tanpa nama) | Closure proxy HTTP ke API Kemdikbud | web | `routes/web.php:161-199` |

### 3.2 `routes/admin.php` (terdaftar, prefix `config('admin_demo.path')`)

Prefix diambil dari `env('ADMIN_PATH', 'admin')` (`config/admin_demo.php:4`). Nilai aktual di mesin ini
**bukan** default; sengaja tidak ditulis di laporan ini karena berfungsi sebagai path rahasia.
Middleware group: `web` + `App\Http\Middleware\HideAdmin` (`bootstrap/app.php:15-17`);
sub-group `admin.only` untuk semua halaman panel (`routes/admin.php:252`).

| Method | URL (relatif prefix) | Nama | Aksi | Middleware tambahan | Lokasi |
|---|---|---|---|---|---|
| GET/HEAD | `/` | (tanpa nama) | Closure redirect ke inbox/login | — | `routes/admin.php:210-215` |
| GET/HEAD | `/login` | `admin.login` | Closure → view `admin.login` | — | `routes/admin.php:217-222` |
| POST | `/login` | `admin.login.post` | Closure cek `config('admin_demo.accounts')` | `throttle:5,1` | `routes/admin.php:224-241` |
| POST | `/logout` | `admin.logout` | Closure hapus session admin | — | `routes/admin.php:243-247` |
| GET/HEAD | `/test-email` | `admin.test.email` | Closure `Mail::raw` | `admin.only` + `admin.only:developer` | `routes/admin.php:257-272` |
| GET/HEAD | `/inbox` | `admin.inbox` | Closure → view `admin.inbox` | `admin.only` | `routes/admin.php:275-277` |
| POST | `/inbox/{id}/approve` | `admin.inbox.approve` | Closure ubah status JSON | `admin.only` | `routes/admin.php:279-297` |
| POST | `/inbox/{id}/reject` | `admin.inbox.reject` | Closure ubah status + email + WA | `admin.only` | `routes/admin.php:299-346` |
| GET/HEAD | `/artikel` | `admin.artikel` | Closure → view `admin.artikel` | `admin.only` | `routes/admin.php:349-351` |
| POST | `/artikel` | `admin.artikel.store` | Closure simpan ke session | `admin.only` | `routes/admin.php:353-364` |
| POST | `/artikel/{id}/delete` | `admin.artikel.delete` | Closure hapus dari session | `admin.only` | `routes/admin.php:366-371` |
| POST | `/artikel/{id}/edit` | `admin.artikel.edit` | Closure ubah session | `admin.only` | `routes/admin.php:373-386` |
| GET/HEAD | `/galeri` | `admin.galeri` | Closure → view `admin.galeri` | `admin.only` | `routes/admin.php:389-391` |
| POST | `/galeri` | `admin.galeri.store` | Closure simpan + upload gambar | `admin.only` | `routes/admin.php:393-416` |
| POST | `/galeri/{id}/delete` | `admin.galeri.delete` | Closure hapus dari session | `admin.only` | `routes/admin.php:418-423` |
| POST | `/galeri/{id}/edit` | `admin.galeri.edit` | Closure ubah + upload gambar | `admin.only` | `routes/admin.php:425-445` |
| GET/HEAD | `/tentang` | `admin.tentang` | `ViewController` → view `admin.tentang` | `admin.only` | `routes/admin.php:448` |
| POST | `/tentang` | `admin.tentang.update` | Closure simpan petinggi + visi ke session | `admin.only` | `routes/admin.php:450-483` |
| GET/HEAD | `/mitra-draf` | `admin.mitra_draf` | Closure → view `admin.mitra_draf` | `admin.only` | `routes/admin.php:485-487` |

### 3.3 Route dari paket (bukan buatan proyek)

| Method | URL | Nama | Aksi | Middleware |
|---|---|---|---|---|
| GET/HEAD | `/forgot-password` | `password.request` | `Fortify\PasswordResetLinkController@create` | web, `guest:web` |
| POST | `/forgot-password` | `password.email` | `Fortify\PasswordResetLinkController@store` | web, `guest:web` |
| GET/HEAD | `/reset-password/{token}` | `password.reset` | `Fortify\NewPasswordController@create` | web, `guest:web` |
| POST | `/reset-password` | `password.update` | `Fortify\NewPasswordController@store` | web, `guest:web` |
| GET/HEAD | `/user/confirm-password` | `password.confirm` | `Fortify\ConfirmablePasswordController@show` | web, `auth:web` |
| POST | `/user/confirm-password` | `password.confirm.store` | `Fortify\ConfirmablePasswordController@store` | web, `auth:web` |
| GET/HEAD | `/user/confirmed-password-status` | `password.confirmation` | `Fortify\ConfirmedPasswordStatusController@show` | web, `auth:web` |
| GET/HEAD, PUT | `/storage/{path}` | `storage.local`, `storage.local.upload` | Closure framework (disk `local`, `serve: true`) | — |
| GET/HEAD | `/up` | — | health check | — |
| POST | `/_boost/browser-logs` | `boost.browser-logs` | Laravel Boost | — |
| GET/HEAD | `/_inertia/devtools/entries[/{id}]` | — | Inertia DevTools | PreventPreviousUrlTracking, web, PreserveFlashData, Authorize |

### 3.4 Route yang TIDAK terdaftar

`bootstrap/app.php:10-19` hanya memuat `routes/web.php`, `routes/console.php`, dan `routes/admin.php`.
Akibatnya:

- **`routes/settings.php` — TIDAK TERDAFTAR.** Route `settings`, `profile.edit`, `profile.update`,
  `profile.destroy`, `security.edit`, `user-password.update`, `appearance.edit` tidak ada.
- **`routes/web.bawaan.php` — TIDAK TERDAFTAR.** Route `home` (`/`) dan `dashboard` (`/dashboard`) tidak ada.
  File ini satu-satunya yang memuat `routes/settings.php` (`routes/web.bawaan.php:11`), jadi
  `app/Http/Controllers/Settings/*`, `app/Http/Requests/Settings/*`, dan seluruh `resources/js/pages/*`
  **tidak punya route aktif**.
- Konsekuensi uji: `tests/Feature/DashboardTest.php:6,14` memanggil `route('dashboard')` dan
  `tests/Feature/ExampleTest.php:4` memanggil `route('home')` → akan gagal karena nama route tidak ada.
- `HandleInertiaRequests` dan `HandleAppearance` (`app/Http/Middleware/`) tidak didaftarkan di
  `bootstrap/app.php:20-24` (hanya alias `admin.only`). Akibatnya props bersama (`auth`, `name`,
  `sidebarOpen`, `appearance`) tidak pernah dikirim ke halaman Inertia.
- Satu-satunya respons Inertia yang masih mungkin terjadi berasal dari view Fortify
  (`forgot-password`, `reset-password`), yang memakai root view bawaan `app`
  (`vendor/inertiajs/inertia-laravel/src/ResponseFactory.php:42` → `resources/views/app.blade.php`).

---

## 4. DAFTAR HALAMAN FRONT-END

### 4.1 `resources/js/pages` (stack React/Inertia)

Sebagian besar halaman di bawah **tidak dapat diakses**: route pemuatnya tidak terdaftar (lihat §3.4).
Kolom "Route" diisi `TIDAK ADA` bila memang tidak ada route aktif.

| Path | Fungsi halaman | Komponen utama | Props Inertia | Route pemuat |
|---|---|---|---|---|
| `resources/js/pages/welcome.tsx` | Halaman sambutan bawaan starter kit Laravel (marketing kit) | `Head`, `Link`, `usePage` | shared props (`auth`) | `TIDAK ADA` (dirujuk `routes/web.bawaan.php:5` sebagai `home`, file tidak dimuat) |
| `resources/js/pages/dashboard.tsx` | Dashboard kosong berisi 4 kotak `PlaceholderPattern` | `PlaceholderPattern`, `dashboard()` wayfinder | tidak ada props | `TIDAK ADA` (dirujuk `routes/web.bawaan.php:8`) |
| `resources/js/pages/auth/login.tsx` | Form login email+password | `Form`, `Input`, `PasswordInput`, `Checkbox`, `Button`, `Spinner`, `InputError`, `TextLink` | `status?`, `canResetPassword` (`app/Providers/FortifyServiceProvider.php:49-52`) | `TIDAK ADA` (Fortify `loginView` tidak terpakai karena `GET /login` sudah dipakai `routes/web.php:101`) |
| `resources/js/pages/auth/forgot-password.tsx` | Form minta tautan reset password | `Form`, `Input`, `Button`, `InputError`, `TextLink` | `status?` (`FortifyServiceProvider.php:60-62`) | `password.request` (GET `/forgot-password`, Fortify) — **dapat diakses**, tetapi tanpa props bersama karena `HandleInertiaRequests` tidak terdaftar |
| `resources/js/pages/auth/reset-password.tsx` | Form password baru + konfirmasi | `Form`, `Input`, `PasswordInput`, `Button`, `Spinner`, `InputError` | `token`, `email`, `passwordRules` (`FortifyServiceProvider.php:54-58`) | `password.reset` (GET `/reset-password/{token}`, Fortify) — **dapat diakses**, idem |
| `resources/js/pages/settings/profile.tsx` | Ubah nama/email + hapus akun | `Form`, `Heading`, `Input`, `Label`, `Button`, `DeleteUser`, `InputError` | `auth` (via `usePage`), `mustVerifyEmail`, `status` (`ProfileController.php:22-25`) | `TIDAK ADA` (`profile.edit` di `routes/settings.php:10`, tidak dimuat) |
| `resources/js/pages/settings/security.tsx` | Ubah password (dan sisa UI 2FA) | `Heading`, `PasswordInput`, `InputError`, `Button`, `SecurityController` (wayfinder) | `passwordRules` (`SecurityController.php:20-24`) | `TIDAK ADA` (`security.edit`, `routes/settings.php:17`) |
| `resources/js/pages/settings/appearance.tsx` | Pilih tema light/dark/system | `Heading`, `AppearanceTabs` | tidak ada props | `TIDAK ADA` (`appearance.edit`, `routes/settings.php:24`) |

Catatan: `GET /forgot-password` dan `GET /reset-password/{token}` terdaftar oleh Fortify dan benar-benar
merender halaman Inertia di atas (root view `resources/views/app.blade.php`). Karena
`HandleInertiaRequests` tidak terdaftar, halaman-halaman itu berjalan tanpa props bersama
(`auth`, `name`, `sidebarOpen`); halaman yang membutuhkannya akan error.

### 4.2 Halaman Blade (UI yang benar-benar tayang)

| Path | Fungsi halaman | Layout | Data yang diterima | Route |
|---|---|---|---|---|
| `resources/views/landing.blade.php` | Beranda publik: hero, statistik, pimpinan, visi-misi, berita, galeri, modal lokasi 8 provinsi | `layouts.user-public` | `session('admin_petinggi')`, `session('admin_visi')`, `session('admin_artikel')`, `session('admin_galeri')`, fallback array di file (baris 6-94) | `landing` |
| `resources/views/pengajuan.blade.php` | Formulir pengajuan 3 langkah + pencarian PT via API PDDIKTI | `layouts.user-public` | `old()`, `$errors`, `session('ok')` | `pengajuan.create` |
| `resources/views/login.blade.php` | Form masuk mitra (demo quick-fill) | `layouts.user-public` | tidak ada | `login` |
| `resources/views/user/status.blade.php` | Status & masa aktif + tabel dokumen | `layouts.user` | `$mitra` (dari session/default) | `mitra.status` |
| `resources/views/user/profile.blade.php` | Edit profil instansi/PIC + unggah logo | `layouts.user` | `$mitra`, `session('ok')` | `mitra.profile` |
| `resources/views/user/draf.blade.php` | "Generator Draf Proposal AI" (simulasi, tanpa backend) | `layouts.user` | `$mitra` | `mitra.draf` |
| `resources/views/admin/login.blade.php` | Login panel admin (mandiri, tanpa layout) | — | `session('info')`, `session('login_error')`, `$errors`, `config('app.debug')` | `admin.login` |
| `resources/views/admin/inbox.blade.php` | Tabel pengajuan + modal review/setujui/tolak + pratinjau surat | `layouts.admin` | `$pengajuan`, `session('ok')`, `session('reject_done')`, `session('surat_preview')`, dll. | `admin.inbox` |
| `resources/views/admin/artikel.blade.php` | CRUD artikel (via partial `_cms`) | `layouts.admin` | `$rows` | `admin.artikel` |
| `resources/views/admin/galeri.blade.php` | CRUD galeri (via partial `_cms`) | `layouts.admin` | `$rows` | `admin.galeri` |
| `resources/views/admin/tentang.blade.php` | Kelola profil petinggi + visi | `layouts.admin` | tidak ada (nilai default hardcoded di form) | `admin.tentang` |
| `resources/views/admin/mitra_draf.blade.php` | Tabel draf mitra & masa aktif (aksi nonaktif) | `layouts.admin` | `$rows` | `admin.mitra_draf` |

Partial: `resources/views/admin/_cms.blade.php` (dipakai artikel & galeri),
`resources/views/partials/header.blade.php` (dipakai `layouts.user-public`),
`resources/views/partials/footer-komdigi.blade.php` (dipakai `layouts.admin`, `layouts.user`, `layouts.user-public`).

---

## 5. LAYOUT DAN KOMPONEN

### 5.1 Layout React (`resources/js/layouts`)

| File | Fungsi | Dipakai |
|---|---|---|
| `layouts/app-layout.tsx` | Pembungkus layout aplikasi (mendelegasikan ke `app/app-sidebar-layout`) | `app.tsx:22` (default semua halaman non-auth) |
| `layouts/auth-layout.tsx` | Pembungkus layout halaman auth (delegasi ke `auth/auth-simple-layout`) | `app.tsx:18` |
| `layouts/app/app-sidebar-layout.tsx` | Shell + sidebar + header + konten | via `app-layout` |
| `layouts/app/app-header-layout.tsx` | Varian shell dengan header saja | tidak diimpor (varian alternatif) |
| `layouts/auth/auth-simple-layout.tsx` | Kartu auth satu kolom | via `auth-layout` |
| `layouts/auth/auth-card-layout.tsx` | Kartu auth dengan slot tambahan | tidak diimpor |
| `layouts/auth/auth-split-layout.tsx` | Auth dua kolom (kiri brand) | tidak diimpor |
| `layouts/settings/layout.tsx` | Navigasi tab settings (profile/security/appearance) | `app.tsx:20` |

### 5.2 Layout Blade (`resources/views/layouts`)

| File | Fungsi | Pemakai |
|---|---|---|
| `layouts/admin.blade.php` | Shell panel admin: sidebar, topbar, `<main>`, alert, footer | 5 view: `admin/inbox`, `admin/artikel`, `admin/galeri`, `admin/tentang`, `admin/mitra_draf` |
| `layouts/user.blade.php` | Shell portal mitra (struktur sama dengan admin) + kartu status masa aktif | 3 view: `user/status`, `user/profile`, `user/draf` |
| `layouts/user-public.blade.php` | Shell publik: header nav, `<main>` (`.wrap-main`), footer, lightbox, modal profil | 3 view: `landing`, `pengajuan`, `login` |

### 5.3 Komponen bersama React (`resources/js/components`)

Jumlah = banyaknya file `.tsx` yang mengimpor komponen tersebut (termasuk impor internal antar komponen).

| File | Fungsi (1 baris) | Jumlah pemakai |
|---|---|---|
| `components/app-logo-icon.tsx` | Ikon logo aplikasi (SVG) | 5 |
| `components/app-logo.tsx` | Logo + teks nama aplikasi | 2 |
| `components/heading.tsx` | Judul + deskripsi bagian | 5 |
| `components/input-error.tsx` | Pesan error field form | 6 |
| `components/password-input.tsx` | Input password dengan tombol lihat/sembunyikan | 4 |
| `components/text-link.tsx` | Tautan teks bergaya Inertia `Link` | 2 |
| `components/breadcrumbs.tsx` | Breadcrumb dari array `BreadcrumbItem` | 2 |
| `components/user-info.tsx` | Avatar + nama/email pengguna | 2 |
| `components/user-menu-content.tsx` | Isi dropdown menu pengguna (profil, logout) | 2 |
| `components/app-content.tsx` | `<main>` + `SidebarInset` | 2 |
| `components/app-shell.tsx` | `SidebarProvider` + layout variant | 2 |
| `components/app-header.tsx` | Header atas + breadcrumb + menu user | 1 |
| `components/app-sidebar.tsx` | Sidebar utama (menu Dashboard, Repository, Documentation) | 1 |
| `components/app-sidebar-header.tsx` | Header sidebar + trigger | 1 |
| `components/appearance-tabs.tsx` | Tab pilihan tema | 1 |
| `components/delete-user.tsx` | Dialog konfirmasi hapus akun | 1 |
| `components/nav-main.tsx` | Daftar menu utama sidebar | 1 |
| `components/nav-footer.tsx` | Daftar tautan footer sidebar | 1 |
| `components/nav-user.tsx` | Blok user di footer sidebar | 1 |
| `components/alert-error.tsx` | Alert daftar error | **0 (tidak dipakai)** |

### 5.4 Komponen UI shadcn (`resources/js/components/ui`)

| File | Fungsi | Jumlah pemakai |
|---|---|---|
| `ui/button.tsx` | Tombol dengan varian CVA | 8 |
| `ui/sidebar.tsx` | Sistem sidebar lengkap (context, provider, menu, sheet) | 7 |
| `ui/label.tsx` | Label form (Radix) | 6 |
| `ui/input.tsx` | Input teks | 5 |
| `ui/dropdown-menu.tsx` | Menu dropdown (Radix) | 3 |
| `ui/avatar.tsx` | Avatar + fallback | 2 |
| `ui/breadcrumb.tsx` | Primitif breadcrumb | 1 |
| `ui/dialog.tsx` | Dialog/modal (Radix) | 1 |
| `ui/navigation-menu.tsx` | Menu navigasi (Radix) | 1 |
| `ui/separator.tsx` | Garis pemisah | 1 |
| `ui/sheet.tsx` | Panel geser (dipakai sidebar mobile) | 1 |
| `ui/sonner.tsx` | Toaster (`sonner`) + `useFlashToast` | 1 |
| `ui/spinner.tsx` | Indikator loading | 2 |
| `ui/tooltip.tsx` | Tooltip (Radix) | 2 |
| `ui/alert.tsx` | Kotak alert | 1 |
| `ui/card.tsx` | Kartu | 1 |
| `ui/checkbox.tsx` | Checkbox (Radix) | 1 |
| `ui/placeholder-pattern.tsx` | Pola SVG pengisi placeholder | 1 |
| `ui/badge.tsx` | Badge | 0 |
| `ui/collapsible.tsx` | Collapsible (Radix) | 0 |
| `ui/icon.tsx` | Pembungkus ikon Lucide | 0 |
| `ui/select.tsx` | Select (Radix) | 0 |
| `ui/skeleton.tsx` | Skeleton loading | 0 |
| `ui/toggle.tsx` | Toggle (Radix) | 0 |
| `ui/toggle-group.tsx` | Toggle group (Radix) | 0 |

### 5.5 Hook (`resources/js/hooks`)

| File | Fungsi |
|---|---|
| `hooks/use-appearance.tsx` | State tema (light/dark/system) + `initializeTheme()` + cookie + `localStorage` |
| `hooks/use-current-url.ts` | Helper `isCurrentUrl`, `isCurrentOrParentUrl`, `whenCurrentUrl` berbasis `usePage().url` |
| `hooks/use-flash-toast.ts` | Menampilkan flash `toast` dari event Inertia ke `sonner` |
| `hooks/use-initials.tsx` | Ambil inisial nama untuk fallback avatar |
| `hooks/use-mobile.tsx` | Deteksi viewport < 768px (`useSyncExternalStore`) |
| `hooks/use-mobile-navigation.ts` | Bersihkan `body.pointer-events` setelah navigasi mobile |
| `hooks/use-clipboard.ts` | Salin teks ke clipboard + status |

### 5.6 Util dan tipe

| File | Isi |
|---|---|
| `resources/js/lib/utils.ts` | `cn()` (clsx + tailwind-merge), `toUrl()` |
| `resources/js/types/auth.ts` | `User`, `Auth` |
| `resources/js/types/navigation.ts` | `BreadcrumbItem`, `NavItem` |
| `resources/js/types/ui.ts` | `AppLayoutProps`, `AppVariant`, `FlashToast`, `AuthLayoutProps` |
| `resources/js/types/index.ts` | Re-export ketiga file di atas |
| `resources/js/types/global.d.ts` | Augmentasi `react` (`passwordrules`) dan `@inertiajs/core` (`sharedPageProps`) |
| `resources/js/types/vite-env.d.ts` | Referensi tipe Vite |
| `resources/js/wayfinder/index.ts` | Helper `query`, `params` untuk route hasil generate |

### 5.7 Komponen Blade

| File | Fungsi | Pemakai |
|---|---|---|
| `resources/views/components/asset.blade.php` | `<link>`/`<script>` aset + cache-busting `?v=filemtime` | 4 layout/halaman (admin, user, user-public, admin/login) |
| `resources/views/partials/header.blade.php` | Navbar publik + menu mobile | `layouts.user-public` |
| `resources/views/partials/footer-komdigi.blade.php` | Footer resmi + tombol kembali ke atas | 3 layout |

---

## 6. DATABASE

### 6.1 Migration (hanya 3 file, semuanya tabel infrastruktur Laravel)

**`database/migrations/0001_01_01_000000_create_users_table.php`**

| Tabel | Kolom | Kunci / Indeks |
|---|---|---|
| `users` | `id` (bigint unsigned, PK auto-increment), `name` (string), `email` (string), `email_verified_at` (timestamp nullable), `password` (string), `remember_token` (string nullable), `created_at`, `updated_at` | unique: `email` (`:17`) |
| `password_reset_tokens` | `email` (string, PK), `token` (string), `created_at` (timestamp nullable) | PK: `email` (`:25`) |
| `sessions` | `id` (string, PK), `user_id` (foreignId nullable), `ip_address` (string 45 nullable), `user_agent` (text nullable), `payload` (longText), `last_activity` (integer) | index: `user_id` (`:32`), index: `last_activity` (`:36`) |

**`..._000001_create_cache_table.php`**

| Tabel | Kolom | Kunci / Indeks |
|---|---|---|
| `cache` | `key` (string, PK), `value` (mediumText), `expiration` (bigint) | index: `expiration` (`:17`) |
| `cache_locks` | `key` (string, PK), `owner` (string), `expiration` (bigint) | index: `expiration` (`:23`) |

**`..._000002_create_jobs_table.php`**

| Tabel | Kolom | Kunci / Indeks |
|---|---|---|
| `jobs` | `id` (PK), `queue` (string), `payload` (longText), `attempts` (unsignedSmallInteger), `reserved_at` (unsignedInteger nullable), `available_at` (unsignedInteger), `created_at` (unsignedInteger) | index: `queue` (`:16`) |
| `job_batches` | `id` (string, PK), `name`, `total_jobs`, `pending_jobs`, `failed_jobs` (integer), `failed_job_ids` (longText), `options` (mediumText nullable), `cancelled_at`, `created_at`, `finished_at` (integer nullable) | PK: `id` |
| `failed_jobs` | `id` (PK), `uuid` (string), `connection` (string), `queue` (string), `payload` (longText), `exception` (longText), `failed_at` (timestamp, default now) | unique: `uuid` (`:39`), index komposit `connection,queue,failed_at` (`:46`) |

**Kunci asing (foreign key):** `TIDAK ADA` deklarasi `foreign()`/`constrained()` di seluruh migration.
`sessions.user_id` hanya `foreignId()->nullable()->index()` tanpa constraint.

**Tabel bisnis:** `TIDAK ADA`. Tidak ada tabel `pengajuan`, `mitra`, `artikel`, `galeri`, `petinggi`,
`dokumen`, atau `mou`. Seluruh data bisnis disimpan di:
- `storage/app/storage_pengajuan.json` (pengajuan; dibuat/ditulis `routes/admin.php:37-60`, `routes/web.php:57-94`),
- session (`admin_artikel`, `admin_galeri`, `admin_petinggi`, `admin_visi`, `admin_draf_mitra`, `mitra_data`),
- array default di `routes/web.php:7-19` dan `routes/admin.php:11-32`.

### 6.2 Model

Hanya satu model: **`app/Models/User.php`**.

| Aspek | Isi |
|---|---|
| Fillable | `#[Fillable(['name','email','password'])]` (`:26`) — atribut PHP, bukan properti `$fillable` |
| Guarded | **TIDAK ADA** (`$guarded` tidak didefinisikan di mana pun) |
| Hidden | `#[Hidden(['password','two_factor_secret','two_factor_recovery_codes','remember_token'])]` (`:27`) |
| Casts | `email_verified_at` → datetime, `password` → hashed (`:38-44`) |
| Relasi | **TIDAK ADA** |
| Trait | `HasFactory`, `Notifiable` (`:31`) |
| ID | **Berurutan** (`$table->id()` / bigint auto-increment). **TIDAK ADA UUID** dan **tidak ada `HasUuids`** |
| Extends | `Illuminate\Foundation\Auth\User` (`:28`) |

### 6.3 Seeder dan factory

| File | Isi |
|---|---|
| `database/seeders/DatabaseSeeder.php` | `User::factory()->create(['name' => 'Test User', 'email' => 'test@example.com'])` (`:20-23`); baris `User::factory(10)` dikomentari (`:18`) |
| `database/factories/UserFactory.php` | `definition()`: nama/email acak, `email_verified_at = now()`, password `password`, `remember_token` (`:25-34`); state `unverified()` (`:39-44`); `withTwoFactor()` **kosong tanpa return** (`:49`) |
| Seeder lain | **TIDAK ADA** |

---

## 7. AUTENTIKASI DAN OTORISASI

### 7.1 Sistem login yang dipakai

Dua sistem berjalan berdampingan dan **tidak terhubung**:

1. **Fortify (terpasang, hampir tidak terpakai).**
   - `laravel/fortify` v1.41.0; provider `app/Providers/FortifyServiceProvider.php`.
   - Fitur diaktifkan hanya `Features::resetPasswords()` (`config/fortify.php:145-147`).
   - Guard `web`, username `email`, home `/dashboard`, limiter `login` (`config/fortify.php:18-119`).
   - Route Fortify yang terdaftar: `forgot-password`, `reset-password`, `user/confirm-password`,
     `user/confirmed-password-status`. **Tidak ada route login/register Fortify yang aktif** —
     `GET /login` dan `POST /login` sudah dipakai `routes/web.php:101-102`.
   - `Fortify::loginView()` merender `auth/login` (Inertia) tetapi route-nya tidak pernah memanggilnya.
2. **Login kustom berbasis session (yang benar-benar dipakai).**
   - **Mitra:** `routes/web.php:101-106`. `POST /login` **tidak memverifikasi apa pun**:
     `fn () => redirect()->route('mitra.status')` (`:102`). Tidak ada `Auth::attempt`, tidak ada
     pengecekan password, tidak ada session yang diset. Halaman mitra selalu memakai data default/session
     (`routes/web.php:109-117,155-158`).
   - **Admin:** `routes/admin.php:217-247`. Membandingkan input dengan akun plaintext di
     `config/admin_demo.php`, memakai `hash_equals` (`:230`), `session()->regenerate()` (`:231`),
     menyimpan `admin_auth = ['name','role']` (`:232`), lalu `redirect()->intended(...)` (`:234`).
     Password disimpan **plaintext** di config (`config/admin_demo.php:11,18`).

### 7.2 Konsep peran dan penegakannya

| Aspek | Kenyataan |
|---|---|
| Kolom role di DB | **TIDAK ADA** (`users` tidak punya kolom role) |
| Model/tabel role | **TIDAK ADA** |
| Peran yang ada | Hanya di session admin: `admin` dan `developer` (`config/admin_demo.php:12,19`) |
| Penegakan | Middleware `admin.only` (`app/Http/Middleware/AdminOnly.php`) + parameter role (`admin.only:developer`) |
| Peran mitra | **TIDAK ADA** — halaman mitra tidak memakai middleware `auth` sama sekali (`routes/web.php:109-158`) |
| Policy | **TIDAK ADA** (folder `app/Policies` tidak ada) |
| Gate | **TIDAK ADA** (`Gate::define` tidak ditemukan) |
| `authorize()` di controller | **TIDAK ADA** |

### 7.3 Middleware kustom

| File | Fungsi |
|---|---|
| `app/Http/Middleware/AdminOnly.php` | Cek `session('admin_auth')`; jika kosong → 401 JSON atau redirect `admin.login` (`:17-24`); jika role tidak cocok → `abort(404)` (`:27-29`) |
| `app/Http/Middleware/HideAdmin.php` | Filter IP dari `config('admin_demo.allowed_ips')` → `abort(404)` (`:13-18`); set header `X-Robots-Tag` dan `Referrer-Policy` (`:21-22`) |
| `app/Http/Middleware/HandleInertiaRequests.php` | Share props `name`, `auth.user`, `sidebarOpen` (`:36-46`) — **tidak terdaftar** |
| `app/Http/Middleware/HandleAppearance.php` | Share `appearance` dari cookie ke view (`:17-21`) — **tidak terdaftar** |

Alias middleware: hanya `admin.only` (`bootstrap/app.php:21-23`).

### 7.4 Session dan cookie

| Setelan | Nilai | Bukti |
|---|---|---|
| Driver | `env('SESSION_DRIVER','database')`; `.env.example:30` → `database` | `config/session.php:21` |
| Lifetime | 120 menit | `config/session.php:35`, `.env.example:31` |
| Encrypt | `false` | `config/session.php:50`, `.env.example:32` |
| `http_only` | `true` (default) | `config/session.php:185` |
| `same_site` | `lax` (default) | `config/session.php:202` |
| `secure` | `env('SESSION_SECURE_COOKIE')` → **null/false** karena tidak ada di `.env.example` | `config/session.php:172` |
| `partitioned` | `false` | `config/session.php:215` |
| Serialization | `json` | `config/session.php:231` |
| Tabel session | `sessions` | `config/session.php:89` |
| Cookie name | slug `APP_NAME` + `-session` | `config/session.php:130-133` |

Catatan: session dipakai sebagai **tempat penyimpanan data bisnis** (profil mitra, artikel, galeri,
petinggi, visi), sehingga isi session bisa membesar (foto base64) dan hilang saat session berakhir.

---

## 8. KEAMANAN SAAT INI (hanya laporan, tidak diperbaiki)

### 8.1 Upload file

| Temuan | Path:baris |
|---|---|
| Logo mitra: **tanpa aturan validasi apa pun** (tidak ada `image`, `mimes`, atau `max`); file dibaca penuh lalu dijadikan data-URI base64 di session | `routes/web.php:120-127` (rules tidak memuat `logo`), `routes/web.php:143-146` |
| Nama file asli tidak disimpan; tidak ada penamaan acak, tidak ada `store()`/`Storage::put()` | `routes/web.php:145`, `routes/admin.php:403,437,467` |
| Galeri: `nullable|image|max:2048` (2 MB) — validasi ada, tetapi hasilnya base64 di session, bukan file di disk | `routes/admin.php:394-397`, `routes/admin.php:400-404`, `routes/admin.php:435-438` |
| Foto petinggi: `p_foto.* => nullable|image|max:2048` — validasi ada, hasil base64 di session | `routes/admin.php:451-458`, `routes/admin.php:464-468` |
| Foto baru dari modal "Tambah Petinggi" dikirim sebagai `nl_foto` tetapi **tidak dibaca controller** (field diabaikan) | `resources/views/admin/tentang.blade.php:130` vs `routes/admin.php:450-483` |
| `enctype="multipart/form-data"` dipasang di form pengajuan padahal tidak ada input file | `resources/views/pengajuan.blade.php:50` |
| `storage:link` **belum pernah dijalankan**: `public/storage` tidak ada; disk `public` tetap didefinisikan | `config/filesystems.php:41-48,76-78`; `Test-Path public/storage` → `False` |
| Disk default `local` punya `serve: true` → route `GET/PUT /storage/{path}` aktif (framework) | `config/filesystems.php:33-39`; route `storage.local` |
| Tidak ada pembersihan file lama saat edit/hapus (karena tidak ada file) | `routes/admin.php:418-445` |

### 8.2 Kueri berisiko

`DB::raw`, `whereRaw`, `orderByRaw`, `selectRaw`, `DB::statement`, `DB::select`, `DB::unprepared`:
**TIDAK ADA** di seluruh kode aplikasi (pencarian `*.php` di luar vendor tidak menemukan satu pun).
Aplikasi hampir tidak memakai query DB; data bisnis di JSON/session.

Risiko terkait I/O non-DB:

| Temuan | Path:baris |
|---|---|
| Baca-tulis JSON tanpa locking → race condition / data hilang saat dua request bersamaan | `routes/web.php:57-58,94`; `routes/admin.php:40-59` |
| `file_put_contents` ke `storage/logs/*.log` dengan `@` (error dibungkam) | `routes/admin.php:160,202` |
| Proxy HTTP keluar dengan **verifikasi TLS dimatikan** (`withoutVerifying()`) dan User-Agent dipalsukan | `routes/web.php:168-174` |
| Panggilan gateway WA memakai `file_get_contents` + `stream_context_create` (bukan HTTP client), error dibungkam | `routes/admin.php:162-176` |

### 8.3 XSS

Tidak ada `dangerouslySetInnerHTML`, `v-html`, `document.write`, atau `eval()` di `resources/js`.
Temuan terpusat di Blade:

| Temuan | Path:baris |
|---|---|
| `dropdown.innerHTML = html` dari respons API eksternal; `escStr()` hanya meng-escape kutip, **tidak** `<`/`&`; `meta` (jenis/kota/provinsi) disisipkan **tanpa escape** | `resources/views/pengajuan.blade.php:1211-1221` (escStr di `:1256-1258`) |
| Handler `onclick` dibangun dengan konkatenasi string berisi data API | `resources/views/pengajuan.blade.php:1213-1214` |
| `info.innerHTML`, `errDiv.innerHTML`, `toast.innerHTML` menyisipkan `msg`/`nama` (sebagian via `escHtml`, sebagian tidak) | `resources/views/pengajuan.blade.php:1252, 1379, 1405` |
| `innerHTML` editor diisi template literal berisi nilai `$mitra['instansi']`, `$mitra['alamat']`, `$mitra['pic']` (dapat diedit mitra sendiri lewat profil) + input form `goal`/`target`/`category` | `resources/views/user/draf.blade.php:131-177` (interpolasi di `:141,148,154,159,165,174`) |
| `bukaEdit('{{ addslashes($r['judul']) }}','{{ addslashes($r['konten']) }}')` — `addslashes` tidak meng-escape `<`, `>`, `&`, `</script>` | `resources/views/admin/_cms.blade.php:58` |
| `onclick="bukaModal({{ json_encode($row) }})"` — seluruh baris data (termasuk input pengguna) disisipkan ke atribut HTML | `resources/views/admin/inbox.blade.php:87` |
| `newCard.innerHTML` memakai `${nama}`, `${jabatan}`, `${badge}`, `${desc}` dari input modal | `resources/views/admin/tentang.blade.php:183-203` |
| `toast.innerHTML = '<span>' + msg + '</span>'` | `resources/views/admin/tentang.blade.php:232`; `resources/views/admin/mitra_draf.blade.php:143` |
| Nilai cookie disisipkan ke dalam `<script>`: `const appearance = '{{ $appearance ?? "system" }}';` (cookie dapat dikendalikan klien) | `resources/views/app.blade.php:10` |
| Banyak `style="..."` inline (bukan XSS, tetapi melanggar aturan proyek dan mempersulit audit) | mis. `resources/views/admin/inbox.blade.php:8-45`, `resources/views/user/profile.blade.php:7-32` |

Catatan positif: `openProfileModal()` memakai `textContent` (`resources/views/layouts/user-public.blade.php:66-71`),
`selectWilayah()` memakai `textContent` (`resources/views/landing.blade.php:332-335`),
dan `@json($wilayah)` (`resources/views/landing.blade.php:317`) aman.

### 8.4 IDOR / BOLA

| Temuan | Path:baris |
|---|---|
| `POST /inbox/{id}/approve` memakai ID berurutan dari file JSON; tidak ada pengecekan pemilik/otorisasi per objek, hanya `admin.only` | `routes/admin.php:279-297` |
| `POST /inbox/{id}/reject` idem; ID tidak ditemukan pun tetap dianggap sukses (status flash `ok` dikirim) | `routes/admin.php:299-346` (`:307-319` pencarian, `:334-345` respons) |
| `POST /artikel/{id}/delete`, `/artikel/{id}/edit`, `/galeri/{id}/delete`, `/galeri/{id}/edit` memakai ID berurutan tanpa scoping | `routes/admin.php:366-386`, `routes/admin.php:418-445` |
| Halaman & aksi mitra **tanpa middleware `auth`**; data diambil dari session/default, tidak ada scoping pemilik | `routes/web.php:109-158` |
| `POST /mitra/profile` dapat mengubah `mitra_data` session siapa pun yang mengakses (tidak ada identitas) | `routes/web.php:119-153` |
| Semua route panel hanya dijaga peran `admin`/`developer`; tidak ada pembedaan hak antar-admin (kecuali `test-email`) | `routes/admin.php:252,272` |
| `GET /api/pddikti` terbuka tanpa autentikasi/throttle → dapat dipakai sebagai proxy pihak ketiga | `routes/web.php:161-199` |
| `GET /test-email?to=...` dapat mengirim email ke alamat sembarang (khusus role developer) | `routes/admin.php:257-272` |

### 8.5 Rate limiting

| Route / limiter | Status | Path:baris |
|---|---|---|
| `POST {ADMIN_PATH}/login` | **ADA** `throttle:5,1` | `routes/admin.php:241` |
| `PUT settings/password` | Ada `throttle:6,1`, tetapi **route tidak terdaftar** | `routes/settings.php:21` |
| Limiter bernama `login` (5/menit per email+IP) | **ADA**, tetapi tidak dipakai route login mana pun | `app/Providers/FortifyServiceProvider.php:72-76`, `config/fortify.php:117-119` |
| `POST /login` (mitra) | **TIDAK ADA** throttle | `routes/web.php:102` |
| `POST /pengajuan` | **TIDAK ADA** throttle | `routes/web.php:24` |
| `GET /api/pddikti` | **TIDAK ADA** throttle | `routes/web.php:161` |
| `POST /mitra/profile` | **TIDAK ADA** throttle | `routes/web.php:119` |

### 8.6 CAPTCHA

**TIDAK ADA.** Pencarian `captcha|recaptcha|hcaptcha|turnstile` di seluruh repo (di luar vendor/node_modules)
tidak menemukan apa pun.

### 8.7 Security headers / CSP

| Header | Status | Path:baris |
|---|---|---|
| `Content-Security-Policy` | **TIDAK ADA** | — |
| `X-Frame-Options` / `frame-ancestors` | **TIDAK ADA** | — |
| `Strict-Transport-Security` | **TIDAK ADA** | — |
| `X-Content-Type-Options` | **TIDAK ADA** | — |
| `X-Robots-Tag: noindex, nofollow, noarchive` | ADA, hanya prefix admin | `app/Http/Middleware/HideAdmin.php:21` |
| `Referrer-Policy: no-referrer` | ADA, hanya prefix admin | `app/Http/Middleware/HideAdmin.php:22` |
| `public/.htaccess` | Hanya rewrite + header Authorization/XSRF; tanpa header keamanan | `public/.htaccess:1-25` |
| `<meta name="robots">` | Ada di layout admin & halaman login admin | `resources/views/layouts/admin.blade.php:6`, `resources/views/admin/login.blade.php:6` |

### 8.8 Validasi

| Lokasi | Pola | Catatan |
|---|---|---|
| `routes/web.php:25-54` | Inline `$request->validate()` di closure | 14 field divalidasi; `jabatan_pic`, `bidang[]`, `pddikti_id` **tidak divalidasi** meski dikirim form (`resources/views/pengajuan.blade.php:96,199,250`) |
| `routes/web.php:120-127` | Inline | `logo` tidak divalidasi (lihat §8.1) |
| `routes/admin.php:300` | Inline | `feedback` `required|string|min:5` |
| `routes/admin.php:354,374` | Inline | `judul` `required|string|max:255` |
| `routes/admin.php:394-397,426-429` | Inline | `judul` + `gambar` |
| `routes/admin.php:451-458` | Inline | `p_nama.*`, `p_jabatan.*`, `p_badge.*`, `p_foto.*`, `p_desc.*`, `visi` |
| `routes/admin.php:279-297` (approve), `366-371` & `418-423` (delete) | **TIDAK ADA validasi** | ID apa pun diterima |
| `routes/admin.php:224-241` (login admin) | **TIDAK ADA validasi** `$request->validate()` | Hanya trim + perbandingan manual |
| `routes/web.php:102` (login mitra) | **TIDAK ADA validasi / autentikasi** | — |
| `routes/web.php:162-165` | Cek panjang query `>= 2` | Bukan validasi formal |
| Form Request | Hanya `app/Http/Requests/Settings/*` (4 file) | **Tidak terpakai** karena `routes/settings.php` tidak dimuat |
| `App\Concerns\ProfileValidationRules` | Trait aturan profil (dipakai `ProfileUpdateRequest`) | Tidak terpakai karena route tidak ada |

### 8.9 Lainnya

| Temuan | Path:baris |
|---|---|
| `$guarded = []` | **TIDAK ADA** (model memakai atribut `#[Fillable]`) |
| `APP_DEBUG=true` di contoh env | `.env.example:4` |
| `APP_KEY=` kosong di contoh env | `.env.example:3` |
| Kredensial plaintext di kode (admin & developer) | `config/admin_demo.php:11,18` |
| Kredensial demo ditampilkan di UI saat `APP_DEBUG` | `resources/views/admin/login.blade.php:34-44` |
| Kredensial mitra demo ditampilkan **tanpa** syarat debug | `resources/views/login.blade.php:10-14` |
| Password default form mitra di-`value` langsung di HTML | `resources/views/login.blade.php:20,24` |
| Email/kontak hardcoded di template surat | `routes/admin.php:68,106` |
| URL `http://127.0.0.1:8000` hardcoded di UI admin | `resources/views/layouts/admin.blade.php:114`; `routes/admin.php:106` |
| Nama/identitas admin hardcoded di UI ("Admin Utama", "Super Administrator") | `resources/views/layouts/admin.blade.php:140-141,182` |
| Token/secret yang tertulis di kode | **TIDAK ADA** (env WA gateway hanya dibaca: `routes/admin.php:162-163`) |
| Variabel env dipakai kode tetapi tidak ada di `.env.example` | `ADMIN_PATH`, `ADMIN_ALLOWED_IPS` (`config/admin_demo.php:4-5`), `WA_GATEWAY_URL`, `WA_GATEWAY_KEY` (`routes/admin.php:162-163`) |
| `.env.example` tidak memuat `SESSION_SECURE_COOKIE`, `SESSION_HTTP_ONLY`, `SESSION_SAME_SITE` | `.env.example:30-34` |
| CSRF | `@csrf` ada di form POST Blade (`login.blade.php:17`, `admin/login.blade.php:65`, `_cms.blade.php:18,62,81`, `inbox.blade.php:137,181`, `user/profile.blade.php:20`) |
| Session fixation admin | Ditangani `session()->regenerate()` (`routes/admin.php:231`) |
| Logout mitra tidak menginvalidasi session/token | `routes/web.php:103-106` (hanya `forget('mitra_data')`) |
| IP allowlist admin bergantung `$request->ip()` (mudah salah bila di belakang proxy) | `app/Http/Middleware/HideAdmin.php:13-16` |

---

## 9. PEMETAAN KE KEBUTUHAN BARU

| Kebutuhan | Status | File terkait |
|---|---|---|
| Peran Superadmin, Admin Kerjasama, Mitra | **BELUM ADA** | Peran hanya `admin`/`developer` di `config/admin_demo.php:6-21`; ditegakkan `app/Http/Middleware/AdminOnly.php`; tidak ada kolom role di `database/migrations/0001_01_01_000000_create_users_table.php:14-22`; tidak ada Policy/Gate |
| Homepage: profil BBLSDM, petinggi & struktur, visi-misi, berita/artikel, galeri, tombol ajukan kerjasama | **PERLU DIUBAH** | `resources/views/landing.blade.php` (semua bagian ada; data default hardcoded `:6-94`; tombol ajukan `:112,139`); sumber data session `admin_petinggi`/`admin_visi`/`admin_artikel`/`admin_galeri` |
| Form pengajuan kerjasama (data instansi, PIC, tujuan & detail) + pencarian perguruan tinggi lewat API | **SUDAH ADA (perlu diubah)** | `resources/views/pengajuan.blade.php:50-347` (field `:78-298`), JS pencarian `:1013-1272`; proxy `routes/web.php:161-199`; validasi `routes/web.php:25-54`; penyimpanan JSON `routes/web.php:57-94` |
| Inbox pengajuan, aksi setujui/tolak | **SUDAH ADA (perlu diubah)** | `resources/views/admin/inbox.blade.php`; `routes/admin.php:275-346`; data `storage/app/storage_pengajuan.json` |
| Pembuatan akun otomatis dan pengiriman email | **BELUM ADA** | Approve hanya mengubah status dan menampilkan pesan "admin dapat mengirimkan kredensial secara terpisah" (`routes/admin.php:296`); tidak ada `User::create`, tidak ada mail kredensial |
| Notifikasi penolakan (email/WhatsApp) | **PERLU DIUBAH** | `routes/admin.php:62-204` (template surat, `Mail::raw`, WA via `api.whatsapp.com` + gateway opsional), respons flash `routes/admin.php:334-345`, UI `resources/views/admin/inbox.blade.php:7-47`; belum ada job/queue, log manual `storage/logs/email_terkirim.log` & `whatsapp_terkirim.log` |
| Login mitra di URL terpisah | **BELUM ADA** | `GET /login` (`routes/web.php:101`) berada di root domain yang sama dengan admin; `POST /login` tidak mengautentikasi (`:102`); tidak ada guard/prefix khusus mitra |
| Halaman mitra: status & masa aktif, detail data, edit profil, perpanjangan, lupa password | **PERLU DIUBAH** | `resources/views/user/status.blade.php`, `user/profile.blade.php`, `user/draf.blade.php`; `layouts/user.blade.php:165-188` (sisa hari & progress hardcoded `:178,181`); perpanjangan dinonaktifkan `layouts/user.blade.php:186`; lupa password hanya route Fortify tanpa view aktif (`config/fortify.php:145-147`) |
| Generator MoU dan template MoU | **BELUM ADA** (hanya simulasi) | `resources/views/user/draf.blade.php:100-179` menyusun teks di browser (tanpa backend, tanpa simpan); tidak ada tabel/template MoU |
| Notifikasi Admin Kerjasama (permintaan password dan perpanjangan) | **BELUM ADA** | Tidak ada route/controller/notifikasi; tombol perpanjangan & unduh draf dinonaktifkan (`resources/views/admin/mitra_draf.blade.php:33-34`); tidak ada model Notification |
| Kelola konten Superadmin (profil, berita, galeri) | **PERLU DIUBAH** | `resources/views/admin/tentang.blade.php`, `admin/artikel.blade.php`, `admin/galeri.blade.php`, partial `admin/_cms.blade.php`; penyimpanan session (`routes/admin.php:349-445,450-483`), belum persisten di DB; tidak ada pemisahan hak superadmin vs admin |

---

## 10. FILE BERISIKO UBAH

File yang dipakai banyak tempat — perubahan di sini berdampak luas:

| File | Peran | Bergantung padanya |
|---|---|---|
| `resources/views/layouts/admin.blade.php` | Shell panel admin (nav, topbar, alert, `<main>`, footer) | 5 view admin (`inbox`, `artikel`, `galeri`, `tentang`, `mitra_draf`) + partial footer |
| `resources/views/layouts/user.blade.php` | Shell portal mitra + kartu status masa aktif | 3 view mitra (`status`, `profile`, `draf`) |
| `resources/views/layouts/user-public.blade.php` | Shell publik + modal lightbox/profil | 3 view publik (`landing`, `pengajuan`, `login`) |
| `resources/views/partials/footer-komdigi.blade.php` | Footer resmi di semua halaman | 3 layout (`admin`, `user`, `user-public`) |
| `resources/views/partials/header.blade.php` | Navbar publik + menu mobile | `layouts.user-public` (3 halaman) |
| `resources/views/components/asset.blade.php` | Cache-busting aset | 4 layout/halaman (`admin`, `user`, `user-public`, `admin/login`) |
| `public/assets/shared/css/tokens.css` | Variabel warna/radius/font | Semua halaman (dimuat di 4 layout/halaman) |
| `public/assets/shared/css/base.css` | Reset, tipografi, fokus, utilitas | Semua halaman |
| `public/assets/shared/css/components.css` | Tombol, kartu, badge, form, tabel, modal | 3 layout (admin, user, user-public) |
| `public/assets/admin/css/style.css` | Gaya panel admin | 2 pemuat (`layouts/admin`, `admin/login`) |
| `public/assets/user/css/style.css` | Gaya portal mitra + publik (32 KB) | 3 layout (`user`, `user-public`) |
| `public/assets/user/css/landing.css` | Gaya khusus landing | `layouts.user-public` (hanya saat route `landing`) |
| `routes/web.php` | Semua route publik + mitra + API | 8 view Blade publik/mitra, `partials/header`, seluruh navigasi mitra |
| `routes/admin.php` | Semua route panel + helper global (`getSharedPengajuanList`, dll.) | 6 view admin, `config/admin_demo.php`, `storage/app/storage_pengajuan.json` |
| `bootstrap/app.php` | Registrasi route, prefix admin, alias middleware | Seluruh routing; jika `routes/settings.php` didaftarkan, semua halaman Inertia ikut aktif |
| `config/admin_demo.php` | Path admin, IP allowlist, akun admin | `bootstrap/app.php:16`, `routes/admin.php:228`, `admin/_cms.blade.php:132`, `HideAdmin` |
| `app/Models/User.php` | Model auth utama | `config/auth.php:67`, `database/seeders/DatabaseSeeder.php`, `database/factories/UserFactory.php`, `tests/*`, `ProfileController`/`SecurityController` |
| `app/Http/Middleware/AdminOnly.php` | Penjaga seluruh panel admin | 18 route admin (lihat §3.2) |
| `app/Http/Middleware/HideAdmin.php` | Penjaga prefix admin + header | Semua route `routes/admin.php` |
| `resources/js/app.tsx` | Bootstrap Inertia + pemilihan layout | Seluruh `resources/js/pages/*` |
| `resources/js/types/*` | Tipe global (`User`, `Auth`, `NavItem`, `BreadcrumbItem`, `FlashToast`) | 20+ komponen & 8 halaman React |
| `resources/js/components/ui/sidebar.tsx` | Primitif sidebar (21 KB) | 7 file (termasuk `app-sidebar`, layout shell) |
| `resources/css/app.css` | Tailwind v4 + token shadcn | Semua halaman Inertia (saat ini tidak tayang) |

File yang **aman diubah tanpa efek luas** (hanya dipakai satu halaman): `admin/inbox.blade.php`,
`admin/tentang.blade.php`, `admin/mitra_draf.blade.php`, `user/status.blade.php`,
`user/profile.blade.php`, `user/draf.blade.php`, `pengajuan.blade.php`, `landing.blade.php`,
`admin/login.blade.php`, `login.blade.php`, `admin/_cms.blade.php` (2 pemakai).

Catatan: tiga file saat ini sudah berstatus **modified** oleh pekerjaan sebelumnya (bukan oleh audit ini):
`resources/views/admin/mitra_draf.blade.php`, `resources/views/layouts/user.blade.php`,
`resources/views/user/draf.blade.php`.

---

## 11. KONVENSI KODE

### 11.1 Penamaan

- **Blade/view:** `snake_case` dengan pemisah folder titik (`user.status`, `admin.mitra_draf`).
  Partial diawali garis bawah (`admin/_cms.blade.php`).
- **Route name:** `dotted.lowercase` (`pengajuan.store`, `mitra.profile.update`, `admin.inbox.approve`).
- **Controller:** `PascalCase` + akhiran `Controller` (`ProfileController`, `SecurityController`).
- **Middleware:** `PascalCase` deskriptif (`AdminOnly`, `HideAdmin`, `HandleInertiaRequests`).
- **Request:** `PascalCase` + `Request` (`ProfileUpdateRequest`).
- **React:** komponen `PascalCase` (`AppHeader`, `UserMenuContent`); file komponen `kebab-case.tsx`
  (`app-header.tsx`, `password-input.tsx`); hook `use-*.ts(x)`; util `camelCase` (`toUrl`).
- **CSS:** kelas `kebab-case`; campuran BEM-ish (`kgf-top`, `pjn-card-head`) dan nama Indonesia
  (`sidebar-link`, `status-badge-active`). Variabel CSS `--brand-600`, `--text-muted`.
- **Bahasa:** komentar kode campur Indonesia/Inggris; teks UI **bahasa Indonesia** (dengan beberapa
  sisa Inggris di stack Inertia: "Log in", "Profile settings", "Forgot password").
- **Data:** field form `snake_case` (`tujuan_mitra`, `metode_pelatihan`, `pembiayaan`).

### 11.2 Struktur komponen

- **Blade:** `@extends(layout)` + `@section('title')` + `@section('content')`; setiap layout menyediakan
  satu `<h1 class="page-main-title">@yield('title')</h1>` dan `<main>`.
- **React:** default export fungsi + objek `.layout` statis untuk breadcrumb/judul
  (`pages/dashboard.tsx:29`, `pages/auth/login.tsx:103`); layout dipilih terpusat di `app.tsx:13-24`.
- **UI kit:** pola shadcn (`cva` variants + `cn()`), Radix primitives di `components/ui`.
- **CSS:** file per area (`admin/style.css`, `user/style.css`, `user/landing.css`) + shared
  (`tokens.css`, `base.css`, `components.css`), dimuat dengan urutan tokens → base → area → components
  (`layouts/admin.blade.php:13-16`).

### 11.3 Pola form

| Stack | Pola | Contoh |
|---|---|---|
| Blade (aktif) | `<form method="POST" action="{{ route(...) }}">` + `@csrf` + `old()` + `$errors` | `resources/views/pengajuan.blade.php:50,82,105`; `user/profile.blade.php:19` |
| Blade admin | Form POST + modal JS + `onsubmit` untuk aksi palsu | `admin/_cms.blade.php:16,60,80`; `admin/tentang.blade.php:15` |
| React | Inertia `<Form {...store.form()}>` | `pages/auth/login.tsx:23`; `pages/settings/profile.tsx` |
| React (aksi) | Wayfinder `Controller` action + `router`/`Form` | `pages/settings/security.tsx:3`; `components/delete-user.tsx:3` |
| react-hook-form | **TIDAK ADA** (tidak ada dependency `react-hook-form` dan tidak ada pemakaian) | — |
| `useForm` Inertia | **TIDAK ADA** pemakaian; memakai `<Form>` | — |

Validasi klien: atribut `required` di HTML + validasi JS manual di `pengajuan.blade.php:1300-1600`
(step wizard, cek checkbox/radio), `novalidate` dipasang di form (`:50`) sehingga validasi browser dimatikan.

### 11.4 Pola validasi server

- Inline di closure route (dominan, lihat §8.8) dengan pesan kustom bahasa Indonesia
  (`routes/web.php:39-54`).
- Form Request hanya untuk settings (tidak aktif).
- Aturan password: `Password::defaults()` → di produksi `min(12)+mixedCase+letters+numbers+symbols+uncompromised`
  (`app/Providers/AppServiceProvider.php:40-48`); di non-produksi `null` (tanpa aturan).

### 11.5 Bahasa teks UI

Bahasa Indonesia baku, dengan penyimpangan:
- istilah campur Inggris: "Review", "Approve Pengajuan", "Generate Draf Proposal AI", "Step 1/3",
  "Logged in as", "Portal Mitra".
- klaim yang tidak dapat diverifikasi ditampilkan sebagai angka: "500+ Mitra Terdaftar",
  "1.2M+ Alumni Pelatihan", "99.8% Kepuasan Mitra" (`landing.blade.php:149-166`).
- teks status tidak konsisten: `AKTIF` (default `routes/web.php:16`) vs `Aktif`
  (`routes/admin.php:30-31`) vs `Disetujui`/`Ditolak`/`Menunggu` (`routes/admin.php:12-14`).

---

## 12. HAL YANG TIDAK JELAS

1. **Nasib stack Inertia/React.** `routes/settings.php` dan `routes/web.bawaan.php` tidak dimuat
   (`bootstrap/app.php:10-19`). Apakah ini sengaja (starter kit dibekukan) atau registrasi route terlewat?
   Jika didaftarkan, `/` akan bentrok (`landing` vs `home`) dan `/dashboard` akan butuh guard.
2. **Uji yang pasti gagal.** `tests/Feature/DashboardTest.php` dan `tests/Feature/ExampleTest.php`
   memakai nama route `dashboard`/`home` yang tidak ada. Apakah uji ini akan dihapus, atau route-nya
   akan diaktifkan?
3. **Sumber kebenaran data.** Data bisnis tersebar di tiga tempat: array default di route, session, dan
   `storage/app/storage_pengajuan.json`. Mana yang dianggap kanonik? Apakah JSON itu data produksi
   (berisi kiriman pengguna nyata) atau hanya data demo?
4. **Login mitra.** `POST /login` hanya redirect (`routes/web.php:102`). Apakah autentikasi mitra memang
   belum dikerjakan, atau ada mekanisme lain (token dari admin) yang tidak terdokumentasi?
5. **Pemisahan URL login mitra.** Kebutuhan menyebut "login mitra di URL terpisah", tetapi saat ini
   admin memakai path rahasia (`ADMIN_PATH`) dan mitra memakai `/login` di root. URL mitra yang
   diinginkan belum ditentukan (mis. `/mitra/login` vs subdomain).
6. **Kredensial demo di produksi.** `resources/views/login.blade.php:10-14` menampilkan akun demo
   **tanpa** syarat `APP_DEBUG` (berbeda dengan `admin/login.blade.php:34`). Apakah ini akan dihapus
   sebelum produksi?
7. **Ambang "segera berakhir".** `AGENTS.md` menetapkan 90 hari dan penyimpanan di satu tempat konfigurasi.
   Di kode, sisa hari masih hardcoded (`layouts/user.blade.php:178` "365 Hari Tersisa",
   `:181` progress 85%). Belum ada file konfigurasi yang menampung ambang tersebut.
8. **Format tanggal.** Ada empat format berbeda: `d F Y` (`routes/web.php:89`), `d/m/Y H:i:s`
   (`routes/admin.php:261`), `translatedFormat('d F Y')` (`routes/admin.php:360`), dan
   `toLocaleDateString('id-ID')` (`admin/mitra_draf.blade.php:123`). Format baku belum ditetapkan.
9. **Lokasi penyimpanan file.** Tidak ada `storage:link` dan semua upload jadi base64 di session.
   Belum jelas apakah target akhirnya disk `public`, object storage, atau tetap base64.
10. **`jabatan_pic`, `bidang[]`, `pddikti_id`.** Dikirim form tetapi tidak divalidasi dan tidak disimpan
    (`routes/web.php:71-91` tidak memuat `jabatan_pic`/`pddikti_id`; `bidang` disimpan tanpa validasi).
    Apakah field ini akan dipakai atau dihapus?
11. **"AI" pada draf proposal.** Semua proses hanya `setTimeout` di browser
    (`resources/views/user/draf.blade.php:114-178`); tidak ada panggilan model/API. Apakah ini akan
    diimplementasikan (Track B) atau diganti label "Segera hadir"?
12. **Modal perpanjangan yang tak terjangkau.** `admin/mitra_draf.blade.php:44-85` masih memuat modal
    `openExtendModal()` tetapi tombol pemicunya sudah `disabled` (`:33-34`), sehingga kode modal menjadi
    dead code. Dihapus atau disimpan untuk Track B?
13. **Gambar eksternal.** Landing memakai 10+ URL `images.unsplash.com` sebagai foto pimpinan/berita/galeri
    (`landing.blade.php:14,21,28,49,55,61,69-74,131`). Apakah boleh ketergantungan ke layanan pihak ketiga
    untuk situs instansi pemerintah?
14. **Duplikasi layout admin/mitra.** `layouts/user.blade.php` menyalin struktur `layouts/admin.blade.php`
    (sidebar, topbar, script `toggleSidebar`). Belum diputuskan apakah akan dijadikan satu layout bersama.
15. **`public/assets/shared/js/ui.js` belum ada.** `AGENTS.md` menetapkan satu sumber JS UI, dan
    `components/asset.blade.php:6` mendokumentasikan pemakaiannya, tetapi file tersebut tidak ada;
    semua JS masih inline per halaman.
