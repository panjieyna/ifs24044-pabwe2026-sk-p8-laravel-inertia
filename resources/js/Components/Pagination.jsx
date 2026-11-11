import { Link } from '@inertiajs/react';
import Icon from './Icon';

/**
 * Navigasi halaman untuk LengthAwarePaginator Laravel.
 */
export default function Pagination({ paginator, label = 'Navigasi halaman' }) {
    if (!paginator || paginator.last_page <= 1) return null;

    const links = paginator.links ?? [];
    const pages = links.slice(1, -1);
    const prev = links[0];
    const next = links[links.length - 1];
    const base = 'inline-flex min-w-10 items-center justify-center rounded-lg px-3 py-2 text-sm font-semibold';

    return (
        <nav aria-label={label} className="flex flex-col items-center justify-between gap-3 sm:flex-row">
            <p className="text-sm text-slate-700">
                Menampilkan <span className="font-semibold">{paginator.from}</span>–<span className="font-semibold">{paginator.to}</span> dari{' '}
                <span className="font-semibold">{paginator.total}</span> data
            </p>
            <ul className="flex flex-wrap items-center gap-1">
                <li>
                    {prev?.url ? (
                        <Link href={prev.url} preserveScroll className={`${base} text-slate-800 hover:bg-slate-200`} aria-label="Halaman sebelumnya">
                            <Icon name="chevronLeft" className="size-4" />
                        </Link>
                    ) : (
                        <span className={`${base} text-slate-500`} aria-hidden="true">
                            <Icon name="chevronLeft" className="size-4" />
                        </span>
                    )}
                </li>
                {pages.map((link, index) => (
                    <li key={`${link.label}-${index}`}>
                        {link.url ? (
                            <Link
                                href={link.url}
                                preserveScroll
                                aria-current={link.active ? 'page' : undefined}
                                aria-label={`Halaman ${link.label}`}
                                className={`${base} ${link.active ? 'bg-blue-700 text-white' : 'text-slate-800 hover:bg-slate-200'}`}
                            >
                                {link.label}
                            </Link>
                        ) : (
                            <span className={`${base} text-slate-600`}>{link.label}</span>
                        )}
                    </li>
                ))}
                <li>
                    {next?.url ? (
                        <Link href={next.url} preserveScroll className={`${base} text-slate-800 hover:bg-slate-200`} aria-label="Halaman berikutnya">
                            <Icon name="chevronRight" className="size-4" />
                        </Link>
                    ) : (
                        <span className={`${base} text-slate-500`} aria-hidden="true">
                            <Icon name="chevronRight" className="size-4" />
                        </span>
                    )}
                </li>
            </ul>
        </nav>
    );
}
