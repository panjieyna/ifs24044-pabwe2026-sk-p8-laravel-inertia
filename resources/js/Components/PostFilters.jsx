import { router } from '@inertiajs/react';
import { useState } from 'react';
import Button from './Button';
import Icon from './Icon';

/**
 * Filter pencarian, status, dan kategori untuk daftar artikel dashboard/admin.
 */
export default function PostFilters({ url, filters, categories, idPrefix = 'filter', showStatus = true }) {
    const [search, setSearch] = useState(filters.search ?? '');

    const visit = (next = {}) => {
        const params = { search, status: filters.status, category: filters.category, ...next };
        Object.keys(params).forEach((key) => !params[key] && delete params[key]);
        router.get(url, params, { preserveScroll: true, preserveState: true, replace: true });
    };

    const fieldClass =
        'block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/30';

    return (
        <form
            role="search"
            aria-label="Filter artikel"
            onSubmit={(event) => {
                event.preventDefault();
                visit();
            }}
            className="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm md:flex-row md:items-center"
        >
            <div className="relative flex-1">
                <label htmlFor={`${idPrefix}-search`} className="sr-only">
                    Cari judul atau isi artikel
                </label>
                <Icon name="search" className="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-slate-500" />
                <input
                    id={`${idPrefix}-search`}
                    name="search"
                    type="search"
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    placeholder="Cari judul atau isi artikel..."
                    className={`${fieldClass} pl-11 placeholder:text-slate-500`}
                />
            </div>
            {showStatus && (
                <div className="md:w-44">
                    <label htmlFor={`${idPrefix}-status`} className="sr-only">
                        Filter status
                    </label>
                    <select id={`${idPrefix}-status`} name="status" value={filters.status ?? ''} onChange={(event) => visit({ status: event.target.value })} className={fieldClass}>
                        <option value="">Semua status</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                    </select>
                </div>
            )}
            <div className="md:w-52">
                <label htmlFor={`${idPrefix}-category`} className="sr-only">
                    Filter kategori
                </label>
                <select id={`${idPrefix}-category`} name="category" value={filters.category ?? ''} onChange={(event) => visit({ category: event.target.value })} className={fieldClass}>
                    <option value="">Semua kategori</option>
                    {categories.map((item) => (
                        <option key={item.value} value={item.value}>
                            {item.label}
                        </option>
                    ))}
                </select>
            </div>
            <Button type="submit" variant="secondary">
                <Icon name="search" className="size-4" />
                Terapkan
            </Button>
        </form>
    );
}
