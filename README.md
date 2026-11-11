# BlogVerse Pro — Studi Kasus P8 PABWE 2026

Platform publikasi artikel multi-tier dengan **Clean Architecture & Feature-First** menggunakan
Laravel 13, Inertia.js (React), PostgreSQL, PhpOffice/PhpSpreadsheet, TOAST UI Editor, dan integrasi OpenAI.

## Tiga Tingkatan Akses

| Tingkatan | URL | Keterangan |
|---|---|---|
| Umum (publik) | `/`, `/articles`, `/articles/{slug}`, `/login`, `/register` | Tanpa login. Sticky top navbar, tanpa sidebar, tautan Login & Register di navbar, hero, akhir artikel, dan footer. |
| Anggota (penulis) | `/dashboard/posts`, `/dashboard/posts/{post}`, `/profile` | CRUD artikel milik sendiri, draf/publikasi, foto sampul, ekspor/impor Excel, AI Generator. |
| Admin | `/admin/users`, `/admin/posts`, `/admin/users/{user}/posts` | Kelola pengguna, monitoring seluruh artikel (read-only), ekspor Excel. |

## Struktur Feature-First

```
app/Features/{Admin,Ai,Auth,Post,Profile}/   Controllers, Services, Repositories, Policies, Enums, DTO, Contracts
app/Http/Middleware/                         EnsurePostOwnership, EnsureRole, HandleInertiaRequests
resources/js/{Layouts,Pages,Components}/     React + Inertia (halaman di-load per chunk)
```

## Menjalankan Secara Lokal

```bash
composer install
npm install --ignore-scripts
cp .env.example .env            # sesuaikan DB_USERNAME / DB_PASSWORD PostgreSQL
php artisan key:generate
psql -U postgres -c "CREATE DATABASE pabwe_p8_blog;"
php artisan migrate --seed
php artisan storage:link
npm run build
php artisan serve
```

Akun hasil seeder:

| Peran | Username | Password |
|---|---|---|
| Admin | `admin` | `admin` |
| Penulis | `member` | `member` |

## Pengujian

```bash
composer test          # atau: test.bat
```

Pengujian memakai SQLite in-memory (lihat `phpunit.xml`) dan wajib mencapai **100% coverage** (lines, methods, elements) pada `app/`.

## Konfigurasi Saat Deploy

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://<username>-pabwe2026-sk-p8-laravel-inertia...
FORCE_HTTPS=true
DB_CONNECTION=pgsql
DB_DATABASE=pabwe_p8_blog
OPENAI_ENDPOINT=...
OPENAI_KEY=...
OPENAI_MODEL=...
```

Setelah deploy jalankan `php artisan migrate --seed --force` dan `php artisan storage:link`.

## Path untuk Audit Lighthouse & Axe

Publik: `/`, `/articles`, `/articles/mengenal-arsitektur-feature-first-di-laravel-13`, `/login`, `/register`

Terproteksi (login sebagai `admin`): `/dashboard/posts`, `/dashboard/posts/1`, `/profile`, `/admin/users`, `/admin/posts`, `/admin/users/2/posts`
