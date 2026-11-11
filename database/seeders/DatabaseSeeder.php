<?php

namespace Database\Seeders;

use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::firstOrCreate(
            ['username' => 'admin'],
            [
                'name' => 'Administrator BlogVerse',
                'email' => 'admin@example.com',
                'password' => 'admin',
                'role' => User::ROLE_ADMIN,
            ]
        );

        $member = User::firstOrCreate(
            ['username' => 'member'],
            [
                'name' => 'Penulis BlogVerse',
                'email' => 'member@example.com',
                'password' => 'member',
                'role' => User::ROLE_MEMBER,
            ]
        );

        foreach ($this->articles() as $index => $article) {
            $author = $article['author'] === 'admin' ? $admin : $member;
            $isPublished = $article['published'];

            Post::firstOrCreate(
                ['slug' => $article['slug']],
                [
                    'user_id' => $author->id,
                    'title' => $article['title'],
                    'category' => $article['category']->value,
                    'excerpt' => $article['excerpt'],
                    'content' => $article['content'],
                    'is_published' => $isPublished,
                    'published_at' => $isPublished ? now()->subDays(12 - $index) : null,
                ]
            );
        }
    }

    /**
     * Artikel contoh.
     *
     * @return array<int, array{author: string, slug: string, title: string, category: PostCategory, excerpt: string, content: string, published: bool}>
     */
    protected function articles(): array
    {
        return [
            [
                'author' => 'member',
                'slug' => 'mengenal-arsitektur-feature-first-di-laravel-13',
                'title' => 'Mengenal Arsitektur Feature-First di Laravel 13',
                'category' => PostCategory::TEKNOLOGI,
                'excerpt' => 'Kelompokkan kode berdasarkan fitur, bukan jenis file, agar proyek Laravel tetap rapi saat aplikasi terus bertumbuh.',
                'content' => <<<'MD'
## Mengapa Feature-First?

Struktur bawaan Laravel memisahkan kode berdasarkan **jenis** file: semua controller di satu folder, semua model di folder lain. Pada proyek kecil cara ini nyaman, tetapi saat fitur bertambah kita harus melompat ke banyak folder hanya untuk memahami satu alur bisnis.

Pendekatan *feature-first* mengelompokkan semua kode milik satu fitur ke dalam satu folder:

- `Controllers` — menerima request dan mengembalikan respons Inertia.
- `Services` — menyimpan aturan bisnis.
- `Repositories` — satu-satunya pintu akses data melalui Eloquent.
- `Policies` dan `Enums` — aturan otorisasi dan nilai tetap.

## Manfaat Utama

1. **Mudah dinavigasi** — semua yang berkaitan dengan artikel ada di `app/Features/Post`.
2. **Mudah diuji** — service dan repository dapat diuji terpisah dari HTTP.
3. **Mudah dikembangkan** — fitur baru cukup ditambahkan sebagai folder baru.

> Kode yang baik bukan hanya berjalan, tetapi juga mudah dipahami oleh rekan satu tim.

## Penutup

Mulailah dari satu fitur, misalnya autentikasi, lalu pindahkan fitur lain secara bertahap. Konsistensi lebih penting daripada kesempurnaan di awal.
MD,
                'published' => true,
            ],
            [
                'author' => 'member',
                'slug' => 'tutorial-dasar-inertiajs-dan-react-untuk-pemula',
                'title' => 'Tutorial Dasar Inertia.js dan React untuk Pemula',
                'category' => PostCategory::PENDIDIKAN,
                'excerpt' => 'Bangun aplikasi satu halaman (SPA) tanpa membuat REST API terpisah dengan menggabungkan Laravel, Inertia.js, dan React.',
                'content' => <<<'MD'
## Apa Itu Inertia.js?

Inertia.js adalah "lem" antara backend Laravel dan frontend React. Controller tetap mengembalikan data, tetapi yang dirender adalah **komponen React**, bukan Blade.

## Langkah Singkat

1. Pasang paket server `inertiajs/inertia-laravel`.
2. Pasang paket klien `@inertiajs/react`, `react`, dan `react-dom`.
3. Buat root template `app.blade.php` dengan direktif `@inertia`.
4. Inisialisasi `createInertiaApp` pada `resources/js/app.jsx`.

```php
return Inertia::render('Public/PostIndex', [
    'posts' => $posts,
]);
```

## Tips Belajar

- Pahami konsep *props* di React terlebih dahulu.
- Gunakan `useForm` untuk menangani formulir beserta pesan validasi.
- Manfaatkan komponen `Link` agar navigasi terasa instan.

Selamat mencoba!
MD,
                'published' => true,
            ],
            [
                'author' => 'member',
                'slug' => 'lima-kebiasaan-kecil-untuk-hari-yang-lebih-produktif',
                'title' => 'Lima Kebiasaan Kecil untuk Hari yang Lebih Produktif',
                'category' => PostCategory::GAYA_HIDUP,
                'excerpt' => 'Produktivitas bukan soal bekerja lebih lama, melainkan membangun kebiasaan kecil yang konsisten setiap hari.',
                'content' => <<<'MD'
## 1. Rencanakan Malam Sebelumnya

Tuliskan tiga prioritas utama untuk esok hari. Pagi hari Anda akan dimulai dengan arah yang jelas.

## 2. Terapkan Teknik Pomodoro

Bekerja fokus selama 25 menit, lalu istirahat 5 menit. Ulangi empat kali, kemudian ambil istirahat lebih panjang.

## 3. Batasi Notifikasi

Matikan notifikasi yang tidak penting. Setiap gangguan kecil dapat menghabiskan banyak waktu untuk kembali fokus.

## 4. Bergerak Secara Rutin

Peregangan ringan atau berjalan kaki sebentar membantu menjaga energi dan suasana hati.

## 5. Evaluasi di Akhir Hari

Catat apa yang berhasil dan apa yang perlu diperbaiki. Refleksi singkat membuat Anda terus bertumbuh.
MD,
                'published' => true,
            ],
            [
                'author' => 'member',
                'slug' => 'membangun-portofolio-digital-untuk-karier-di-bidang-teknologi',
                'title' => 'Membangun Portofolio Digital untuk Karier di Bidang Teknologi',
                'category' => PostCategory::BISNIS,
                'excerpt' => 'Portofolio yang baik menunjukkan cara Anda berpikir dan memecahkan masalah, bukan hanya daftar proyek.',
                'content' => <<<'MD'
## Pilih Proyek Terbaik

Tampilkan tiga sampai lima proyek yang paling mewakili kemampuan Anda. Kualitas jauh lebih penting daripada kuantitas.

## Ceritakan Prosesnya

Untuk setiap proyek, jelaskan:

- **Masalah** yang ingin diselesaikan.
- **Solusi** dan teknologi yang dipilih.
- **Hasil** yang dapat diukur, misalnya waktu muat yang lebih cepat.

## Jaga Agar Tetap Mudah Diakses

Pastikan portofolio cepat dibuka di ponsel, memiliki kontras warna yang baik, dan dapat dinavigasi dengan keyboard. Perekrut sering membukanya di sela kesibukan.

## Perbarui Secara Berkala

Jadwalkan pembaruan setiap tiga bulan agar portofolio selalu mencerminkan kemampuan terbaru Anda.
MD,
                'published' => true,
            ],
            [
                'author' => 'admin',
                'slug' => 'aksesibilitas-web-bukan-fitur-tambahan',
                'title' => 'Aksesibilitas Web Bukan Fitur Tambahan',
                'category' => PostCategory::OPINI,
                'excerpt' => 'Website yang aksesibel memberi kesempatan yang sama bagi semua orang untuk membaca, belajar, dan berpartisipasi.',
                'content' => <<<'MD'
## Untuk Siapa Kita Membangun?

Pengguna kita beragam: ada yang memakai pembaca layar, ada yang hanya menggunakan keyboard, ada pula yang membuka situs di ponsel dengan koneksi lambat.

## Prinsip Sederhana

- Gunakan elemen HTML semantik seperti `header`, `nav`, `main`, dan `footer`.
- Berikan label yang jelas pada setiap input formulir.
- Pastikan kontras warna teks memadai.
- Sertakan teks alternatif pada gambar yang bermakna.

## Penutup

Aksesibilitas sebaiknya dirancang sejak awal. Memperbaikinya di akhir proyek selalu lebih mahal dan melelahkan.
MD,
                'published' => true,
            ],
            [
                'author' => 'admin',
                'slug' => 'selamat-datang-di-blogverse-pro',
                'title' => 'Selamat Datang di BlogVerse Pro',
                'category' => PostCategory::UMUM,
                'excerpt' => 'BlogVerse Pro adalah ruang berbagi tulisan seputar teknologi, pendidikan, gaya hidup, bisnis, dan opini.',
                'content' => <<<'MD'
## Tentang BlogVerse Pro

BlogVerse Pro adalah platform publikasi artikel dengan tiga tingkatan akses: **pembaca umum**, **penulis**, dan **admin**.

## Mulai Menulis

1. Daftar sebagai penulis melalui halaman Register.
2. Masuk ke Dashboard Penulis.
3. Tulis artikel dengan editor Markdown, unggah foto sampul, lalu publikasikan.

Butuh inspirasi? Gunakan fitur **AI Generator** untuk merancang draf artikel secara instan.
MD,
                'published' => true,
            ],
            [
                'author' => 'member',
                'slug' => 'draf-rencana-seri-tulisan-clean-architecture',
                'title' => 'Draf: Rencana Seri Tulisan Clean Architecture',
                'category' => PostCategory::TEKNOLOGI,
                'excerpt' => 'Kerangka awal seri tulisan tentang penerapan Clean Architecture pada proyek Laravel.',
                'content' => <<<'MD'
## Kerangka Seri

1. Memisahkan aturan bisnis dari framework.
2. Repository pattern dengan Eloquent.
3. Menguji service dengan PHPUnit hingga coverage 100%.
MD,
                'published' => false,
            ],
        ];
    }
}
