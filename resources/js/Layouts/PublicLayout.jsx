import { Link, usePage } from '@inertiajs/react';
import Brand from '../Components/Brand';
import { Avatar } from '../Components/Display';
import GlobalLoading from '../Components/GlobalLoading';
import Icon from '../Components/Icon';

const NAV_LINKS = [
    { href: '/', label: 'Beranda', match: (url) => url === '/' },
    { href: '/articles', label: 'Artikel', match: (url) => url.startsWith('/articles') },
];

const FOOTER_CATEGORIES = ['Teknologi', 'Pendidikan', 'Gaya Hidup', 'Bisnis', 'Opini', 'Umum'];

/**
 * Layout halaman publik: Sticky Top Navbar + area baca lebar penuh + footer editorial (tanpa sidebar).
 */
export default function PublicLayout({ children }) {
    const page = usePage();
    const user = page.props.auth?.user;
    const path = page.url.split('?')[0];

    return (
        <div className="flex min-h-screen flex-col bg-slate-50">
            <a
                href="#konten-utama"
                className="sr-only z-50 rounded-lg bg-blue-700 px-4 py-2 font-semibold text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4"
            >
                Lewati ke konten utama
            </a>

            <header className="sticky top-0 z-40 border-b border-slate-200 bg-white/95 backdrop-blur">
                <nav aria-label="Navigasi utama" className="mx-auto max-w-6xl px-4 sm:px-6">
                    <div className="flex items-center justify-between gap-4 py-3">
                        <Brand />

                        <ul className="hidden items-center gap-1 md:flex">
                            {NAV_LINKS.map((link) => (
                                <li key={link.href}>
                                    <Link
                                        href={link.href}
                                        aria-current={link.match(path) ? 'page' : undefined}
                                        className={`rounded-lg px-3 py-2 text-sm font-semibold ${
                                            link.match(path) ? 'bg-blue-50 text-blue-800' : 'text-slate-700 hover:bg-slate-100 hover:text-slate-900'
                                        }`}
                                    >
                                        {link.label}
                                    </Link>
                                </li>
                            ))}
                        </ul>

                        <div className="flex items-center gap-2">
                            {user ? (
                                <>
                                    <Link
                                        href={user.role === 'admin' ? '/admin/users' : '/dashboard/posts'}
                                        className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-3.5 py-2 text-sm font-semibold text-white hover:bg-blue-800"
                                    >
                                        <Icon name={user.role === 'admin' ? 'shield' : 'grid'} className="size-4" />
                                        {user.role === 'admin' ? 'Panel Admin' : 'Dashboard Penulis'}
                                    </Link>
                                    <Link href="/profile" className="rounded-full" aria-label={`Profil ${user.name}`}>
                                        <Avatar name={user.name} src={user.profile_photo_url} size="size-9" />
                                    </Link>
                                </>
                            ) : (
                                <>
                                    <Link
                                        href="/login"
                                        className="inline-flex items-center gap-1.5 rounded-xl px-3 py-2 text-sm font-semibold text-slate-800 hover:bg-slate-100"
                                    >
                                        <Icon name="login" className="size-4" />
                                        Masuk
                                    </Link>
                                    <Link
                                        href="/register"
                                        className="inline-flex items-center gap-1.5 rounded-xl bg-blue-700 px-3.5 py-2 text-sm font-semibold text-white hover:bg-blue-800"
                                    >
                                        <Icon name="userPlus" className="size-4" />
                                        Daftar
                                    </Link>
                                </>
                            )}
                        </div>
                    </div>

                    <ul className="-mt-1 flex items-center gap-1 pb-2 md:hidden">
                        {NAV_LINKS.map((link) => (
                            <li key={link.href}>
                                <Link
                                    href={link.href}
                                    aria-current={link.match(path) ? 'page' : undefined}
                                    className={`inline-block rounded-lg px-3 py-2 text-sm font-semibold ${
                                        link.match(path) ? 'bg-blue-50 text-blue-800' : 'text-slate-700 hover:bg-slate-100'
                                    }`}
                                >
                                    {link.label}
                                </Link>
                            </li>
                        ))}
                    </ul>
                </nav>
            </header>

            <main id="konten-utama" tabIndex={-1} className="flex-1 focus:outline-none">
                {children}
            </main>

            <footer className="mt-16 bg-slate-900 text-slate-300">
                <div className="mx-auto grid max-w-6xl gap-10 px-4 py-12 sm:px-6 md:grid-cols-4">
                    <div className="md:col-span-2">
                        <Brand inverted />
                        <p className="mt-4 max-w-md text-sm leading-relaxed text-slate-300">
                            BlogVerse Pro adalah ruang editorial untuk berbagi gagasan seputar teknologi, pendidikan, gaya hidup, bisnis, dan opini. Baca
                            gratis tanpa login, atau bergabunglah sebagai penulis.
                        </p>
                    </div>

                    <nav aria-label="Navigasi footer">
                        <h2 className="text-sm font-bold uppercase tracking-wider text-white">Jelajahi</h2>
                        <ul className="mt-4 space-y-1 text-sm">
                            <li>
                                <Link href="/" className="inline-block py-1 hover:text-white hover:underline">
                                    Beranda Publik
                                </Link>
                            </li>
                            <li>
                                <Link href="/articles" className="inline-block py-1 hover:text-white hover:underline">
                                    Semua Artikel
                                </Link>
                            </li>
                            <li>
                                <Link href="/login" className="inline-block py-1 hover:text-white hover:underline">
                                    Halaman Login
                                </Link>
                            </li>
                            <li>
                                <Link href="/register" className="inline-block py-1 hover:text-white hover:underline">
                                    Halaman Register
                                </Link>
                            </li>
                        </ul>
                    </nav>

                    <nav aria-label="Kategori artikel">
                        <h2 className="text-sm font-bold uppercase tracking-wider text-white">Kategori</h2>
                        <ul className="mt-4 space-y-1 text-sm">
                            {FOOTER_CATEGORIES.map((category) => (
                                <li key={category}>
                                    <Link
                                        href={`/articles?category=${encodeURIComponent(category)}`}
                                        className="inline-block py-1 hover:text-white hover:underline"
                                    >
                                        {category}
                                    </Link>
                                </li>
                            ))}
                        </ul>
                    </nav>
                </div>
                <div className="border-t border-slate-800">
                    <p className="mx-auto max-w-6xl px-4 py-5 text-sm text-slate-300 sm:px-6">
                        &copy; {new Date().getFullYear()} BlogVerse Pro. Dibangun dengan Laravel 13, Inertia.js &amp; React.
                    </p>
                </div>
            </footer>

            <GlobalLoading />
        </div>
    );
}
