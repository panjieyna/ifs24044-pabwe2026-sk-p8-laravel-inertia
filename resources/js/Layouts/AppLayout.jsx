import { Link, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import Brand from '../Components/Brand';
import { Avatar } from '../Components/Display';
import FlashMessage from '../Components/FlashMessage';
import GlobalLoading from '../Components/GlobalLoading';
import Icon from '../Components/Icon';

function navSections(user) {
    const sections = [
        {
            title: 'Penulis',
            links: [
                { href: '/dashboard/posts', label: 'Artikel Saya', icon: 'document', match: (path) => path.startsWith('/dashboard/posts') },
                { href: '/profile', label: 'Profil Saya', icon: 'user', match: (path) => path.startsWith('/profile') },
            ],
        },
    ];

    if (user?.role === 'admin') {
        sections.push({
            title: 'Admin',
            links: [
                { href: '/admin/users', label: 'Kelola Pengguna', icon: 'users', match: (path) => path.startsWith('/admin/users') },
                { href: '/admin/posts', label: 'Semua Artikel', icon: 'grid', match: (path) => path.startsWith('/admin/posts') },
            ],
        });
    }

    sections.push({
        title: 'Lainnya',
        links: [{ href: '/', label: 'Lihat Blog Publik', icon: 'globe', match: () => false }],
    });

    return sections;
}

function Sidebar({ user, path, onNavigate }) {
    return (
        <div className="flex h-full flex-col bg-slate-900 text-slate-200">
            <div className="flex h-16 items-center border-b border-slate-800 px-5">
                <Brand inverted href="/dashboard/posts" />
            </div>

            <nav aria-label="Menu dashboard" className="flex-1 space-y-6 overflow-y-auto px-3 py-5">
                {navSections(user).map((section) => (
                    <div key={section.title}>
                        <p className="px-3 text-xs font-bold uppercase tracking-wider text-slate-300">{section.title}</p>
                        <ul className="mt-2 space-y-1">
                            {section.links.map((link) => {
                                const active = link.match(path);
                                return (
                                    <li key={link.href}>
                                        <Link
                                            href={link.href}
                                            onClick={onNavigate}
                                            aria-current={active ? 'page' : undefined}
                                            className={`flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-semibold ${
                                                active ? 'bg-blue-700 text-white' : 'text-slate-200 hover:bg-slate-800 hover:text-white'
                                            }`}
                                        >
                                            <Icon name={link.icon} className="size-5" />
                                            {link.label}
                                        </Link>
                                    </li>
                                );
                            })}
                        </ul>
                    </div>
                ))}
            </nav>

            <div className="border-t border-slate-800 p-4">
                <div className="flex items-center gap-3">
                    <Avatar name={user?.name} src={user?.profile_photo_url} />
                    <div className="min-w-0">
                        <p className="truncate text-sm font-semibold text-white">{user?.name}</p>
                        <p className="truncate text-xs text-slate-300">
                            @{user?.username} · {user?.role === 'admin' ? 'Admin' : 'Penulis'}
                        </p>
                    </div>
                </div>
                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    className="mt-4 flex w-full items-center justify-center gap-2 rounded-xl border border-slate-600 px-3 py-2 text-sm font-semibold text-slate-100 hover:bg-slate-800"
                >
                    <Icon name="logout" className="size-4" />
                    Keluar
                </Link>
            </div>
        </div>
    );
}

/**
 * Layout dashboard (penulis & admin) dengan sidebar.
 */
export default function AppLayout({ title, subtitle, actions, back, children }) {
    const { auth } = usePage().props;
    const path = usePage().url.split('?')[0];
    const [mobileOpen, setMobileOpen] = useState(false);

    useEffect(() => {
        if (!mobileOpen) return undefined;
        const handleKey = (event) => event.key === 'Escape' && setMobileOpen(false);
        document.addEventListener('keydown', handleKey);
        return () => document.removeEventListener('keydown', handleKey);
    }, [mobileOpen]);

    return (
        <div className="min-h-screen bg-slate-100">
            <a
                href="#konten-utama"
                className="sr-only z-[60] rounded-lg bg-blue-700 px-4 py-2 font-semibold text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4"
            >
                Lewati ke konten utama
            </a>

            <aside aria-label="Sidebar" className="fixed inset-y-0 left-0 z-30 hidden w-64 lg:block">
                <Sidebar user={auth.user} path={path} />
            </aside>

            {mobileOpen && (
                <div className="fixed inset-0 z-50 lg:hidden">
                    <button type="button" className="absolute inset-0 bg-slate-900/60" aria-label="Tutup menu" onClick={() => setMobileOpen(false)} />
                    <aside aria-label="Sidebar seluler" className="animate-fade-in absolute inset-y-0 left-0 w-72 max-w-[85%] shadow-2xl">
                        <Sidebar user={auth.user} path={path} onNavigate={() => setMobileOpen(false)} />
                    </aside>
                </div>
            )}

            <div className="lg:pl-64">
                <header className="sticky top-0 z-20 flex h-16 items-center gap-3 border-b border-slate-200 bg-white px-4 sm:px-6 lg:hidden">
                    <button
                        type="button"
                        onClick={() => setMobileOpen(true)}
                        className="rounded-lg p-2 text-slate-800 hover:bg-slate-100"
                        aria-label="Buka menu navigasi"
                        aria-expanded={mobileOpen}
                    >
                        <Icon name="menu" className="size-6" />
                    </button>
                    <Brand href="/dashboard/posts" />
                </header>

                <main id="konten-utama" tabIndex={-1} className="mx-auto max-w-6xl px-4 py-6 focus:outline-none sm:px-6 lg:py-8">
                    {back && (
                        <Link href={back.href} className="mb-4 inline-flex items-center gap-1.5 rounded-lg py-1 text-sm font-semibold text-blue-700 hover:underline">
                            <Icon name="arrowLeft" className="size-4" />
                            {back.label}
                        </Link>
                    )}

                    <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                        <div className="min-w-0">
                            <h1 className="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">{title}</h1>
                            {subtitle && <p className="mt-1 text-sm text-slate-700">{subtitle}</p>}
                        </div>
                        {actions && <div className="flex flex-wrap items-center gap-2">{actions}</div>}
                    </div>

                    <div className="mb-6 empty:hidden">
                        <FlashMessage />
                    </div>

                    {children}
                </main>
            </div>

            <GlobalLoading />
        </div>
    );
}
