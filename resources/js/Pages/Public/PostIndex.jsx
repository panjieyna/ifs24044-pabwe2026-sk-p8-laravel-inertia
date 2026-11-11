import { Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Button from '../../Components/Button';
import { EmptyState } from '../../Components/Display';
import Icon from '../../Components/Icon';
import Pagination from '../../Components/Pagination';
import PostCard from '../../Components/PostCard';
import Seo from '../../Components/Seo';
import PublicLayout from '../../Layouts/PublicLayout';

function Hero({ user }) {
    return (
        <section className="bg-slate-900 text-white">
            <div className="mx-auto grid max-w-6xl gap-10 px-4 py-14 sm:px-6 md:grid-cols-5 md:py-20">
                <div className="md:col-span-3">
                    <p className="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-sm font-semibold text-amber-300">
                        <Icon name="sparkles" className="size-4" />
                        Platform publikasi artikel multi-tier
                    </p>
                    <h1 className="mt-5 text-4xl font-extrabold leading-tight tracking-tight sm:text-5xl">
                        Baca, tulis, dan bagikan gagasan terbaikmu di <span className="text-amber-300">BlogVerse Pro</span>
                    </h1>
                    <p className="mt-5 max-w-xl text-lg leading-relaxed text-slate-200">
                        Jelajahi artikel teknologi, pendidikan, gaya hidup, bisnis, dan opini tanpa perlu login. Ingin menulis? Masuk atau daftar
                        sebagai penulis untuk mulai mempublikasikan karya Anda.
                    </p>

                    <div className="mt-8 flex flex-wrap gap-3">
                        {user ? (
                            <Button href={user.role === 'admin' ? '/admin/users' : '/dashboard/posts'} size="lg" className="bg-amber-400 text-slate-900 hover:bg-amber-300">
                                <Icon name="grid" className="size-5" />
                                Buka {user.role === 'admin' ? 'Panel Admin' : 'Dashboard Penulis'}
                            </Button>
                        ) : (
                            <>
                                <Link
                                    href="/login"
                                    className="inline-flex items-center gap-2 rounded-xl bg-amber-400 px-5 py-3 text-base font-semibold text-slate-900 hover:bg-amber-300"
                                >
                                    <Icon name="login" className="size-5" />
                                    Halaman Login (Masuk)
                                </Link>
                                <Link
                                    href="/register"
                                    className="inline-flex items-center gap-2 rounded-xl border border-white/40 px-5 py-3 text-base font-semibold text-white hover:bg-white/10"
                                >
                                    <Icon name="userPlus" className="size-5" />
                                    Halaman Register (Daftar Penulis)
                                </Link>
                            </>
                        )}
                    </div>
                </div>

                <div className="hidden md:col-span-2 md:block">
                    <div className="rounded-3xl border border-white/10 bg-white/5 p-6">
                        <p className="text-sm font-semibold uppercase tracking-wider text-amber-300">Kenapa BlogVerse Pro?</p>
                        <ul className="mt-4 space-y-4 text-sm text-slate-200">
                            {[
                                ['bookOpen', 'Baca gratis', 'Semua artikel publik dapat dibaca tanpa akun.'],
                                ['pencil', 'Editor Markdown', 'Menulis nyaman dengan TOAST UI Editor.'],
                                ['sparkles', 'AI Generator', 'Rancang draf artikel instan dengan OpenAI.'],
                                ['download', 'Ekspor & Impor Excel', 'Kelola artikel massal dengan mudah.'],
                            ].map(([icon, title, text]) => (
                                <li key={title} className="flex gap-3">
                                    <span className="inline-flex size-9 shrink-0 items-center justify-center rounded-xl bg-blue-600 text-white">
                                        <Icon name={icon} className="size-5" />
                                    </span>
                                    <span>
                                        <span className="block font-semibold text-white">{title}</span>
                                        {text}
                                    </span>
                                </li>
                            ))}
                        </ul>
                    </div>
                </div>
            </div>
        </section>
    );
}

export default function PostIndex({ posts, filters, categories, isHome }) {
    const { auth } = usePage().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [category, setCategory] = useState(filters.category ?? '');

    const applyFilters = (next) => {
        const params = { search, category, ...next };
        Object.keys(params).forEach((key) => !params[key] && delete params[key]);
        router.get('/articles', params, { preserveScroll: !isHome, preserveState: true });
    };

    const submit = (event) => {
        event.preventDefault();
        applyFilters({});
    };

    const hasFilter = Boolean(filters.search || filters.category);
    const activeCategory = categories.find((item) => item.value === filters.category);

    return (
        <PublicLayout>
            <Seo
                title={isHome ? 'Beranda' : activeCategory ? `Artikel ${activeCategory.label}` : 'Semua Artikel'}
                description={
                    isHome
                        ? 'Baca artikel terbaru seputar teknologi, pendidikan, gaya hidup, bisnis, dan opini di BlogVerse Pro tanpa perlu login.'
                        : 'Cari dan filter seluruh artikel publik BlogVerse Pro berdasarkan judul, konten, dan kategori.'
                }
            />

            {isHome ? (
                <Hero user={auth?.user} />
            ) : (
                <section className="border-b border-slate-200 bg-white">
                    <div className="mx-auto max-w-6xl px-4 py-10 sm:px-6">
                        <nav aria-label="Breadcrumb" className="text-sm text-slate-700">
                            <ol className="flex items-center gap-1.5">
                                <li>
                                    <Link href="/" className="hover:text-blue-800 hover:underline">
                                        Beranda
                                    </Link>
                                </li>
                                <li aria-hidden="true">/</li>
                                <li aria-current="page" className="font-semibold text-slate-900">
                                    Artikel
                                </li>
                            </ol>
                        </nav>
                        <h1 className="mt-3 text-3xl font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                            {activeCategory ? activeCategory.label : 'Semua Artikel'}
                        </h1>
                        <p className="mt-2 max-w-2xl text-slate-700">
                            Telusuri tulisan dari komunitas penulis BlogVerse Pro. Gunakan pencarian dan filter kategori untuk menemukan bacaan favorit.
                        </p>
                    </div>
                </section>
            )}

            <div className="mx-auto max-w-6xl px-4 py-10 sm:px-6">
                <form onSubmit={submit} role="search" aria-label="Cari artikel" className="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row">
                    <div className="relative flex-1">
                        <label htmlFor="public-search" className="sr-only">
                            Cari artikel berdasarkan judul atau konten
                        </label>
                        <Icon name="search" className="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-slate-500" />
                        <input
                            id="public-search"
                            name="search"
                            type="search"
                            value={search}
                            onChange={(event) => setSearch(event.target.value)}
                            placeholder="Cari judul atau isi artikel..."
                            className="block w-full rounded-xl border border-slate-300 py-2.5 pl-11 pr-3 text-sm text-slate-900 placeholder:text-slate-500 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/30"
                        />
                    </div>
                    <div className="sm:w-56">
                        <label htmlFor="public-category" className="sr-only">
                            Filter kategori
                        </label>
                        <select
                            id="public-category"
                            name="category"
                            value={category}
                            onChange={(event) => {
                                setCategory(event.target.value);
                                applyFilters({ category: event.target.value });
                            }}
                            className="block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/30"
                        >
                            <option value="">Semua kategori</option>
                            {categories.map((item) => (
                                <option key={item.value} value={item.value}>
                                    {item.label}
                                </option>
                            ))}
                        </select>
                    </div>
                    <Button type="submit">
                        <Icon name="search" className="size-4" />
                        Cari
                    </Button>
                </form>

                <ul className="mt-5 flex flex-wrap gap-2" aria-label="Kategori populer">
                    <li>
                        <Link
                            href="/articles"
                            aria-current={!filters.category ? 'true' : undefined}
                            className={`inline-block rounded-full px-3.5 py-1.5 text-sm font-semibold ${
                                !filters.category ? 'bg-blue-700 text-white' : 'bg-white text-slate-800 ring-1 ring-slate-300 hover:bg-slate-100'
                            }`}
                        >
                            Semua
                        </Link>
                    </li>
                    {categories.map((item) => (
                        <li key={item.value}>
                            <Link
                                href={`/articles?category=${encodeURIComponent(item.value)}`}
                                aria-current={filters.category === item.value ? 'true' : undefined}
                                className={`inline-block rounded-full px-3.5 py-1.5 text-sm font-semibold ${
                                    filters.category === item.value ? 'bg-blue-700 text-white' : 'bg-white text-slate-800 ring-1 ring-slate-300 hover:bg-slate-100'
                                }`}
                            >
                                {item.value}
                            </Link>
                        </li>
                    ))}
                </ul>

                <section aria-labelledby="daftar-artikel" className="mt-10">
                    <div className="mb-5 flex flex-wrap items-end justify-between gap-3">
                        <h2 id="daftar-artikel" className="text-2xl font-bold tracking-tight text-slate-900">
                            {isHome ? 'Artikel Terbaru' : hasFilter ? 'Hasil Pencarian' : 'Daftar Artikel'}
                        </h2>
                        <p className="text-sm text-slate-700">{posts.total} artikel ditemukan</p>
                    </div>

                    {posts.data.length === 0 ? (
                        <EmptyState
                            title="Belum ada artikel yang cocok"
                            description="Coba kata kunci lain atau pilih kategori berbeda."
                            action={
                                hasFilter && (
                                    <Button href="/articles" variant="secondary">
                                        Reset filter
                                    </Button>
                                )
                            }
                        />
                    ) : (
                        <div className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                            {posts.data.map((post, index) => (
                                <PostCard key={post.id} post={post} categories={categories} priority={index < 3} />
                            ))}
                        </div>
                    )}

                    <div className="mt-8">
                        <Pagination paginator={posts} label="Navigasi halaman artikel" />
                    </div>
                </section>
            </div>
        </PublicLayout>
    );
}
