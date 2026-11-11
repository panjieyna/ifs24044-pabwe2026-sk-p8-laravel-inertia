import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import Button from '../../Components/Button';
import { Avatar, EmptyState, StatCard } from '../../Components/Display';
import Icon from '../../Components/Icon';
import Pagination from '../../Components/Pagination';
import Seo from '../../Components/Seo';
import AppLayout from '../../Layouts/AppLayout';
import { formatDate, numberFormat } from '../../utils/format';

export default function UserList({ users, stats, filters }) {
    const [search, setSearch] = useState(filters.search ?? '');

    const visit = (next = {}) => {
        const params = { search, role: filters.role, ...next };
        Object.keys(params).forEach((key) => !params[key] && delete params[key]);
        router.get('/admin/users', params, { preserveScroll: true, preserveState: true, replace: true });
    };

    const fieldClass =
        'block w-full rounded-xl border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/30';

    return (
        <AppLayout
            title="Kelola Pengguna"
            subtitle="Daftar seluruh pengguna beserta jumlah artikel yang mereka tulis."
            actions={
                <Button variant="secondary" href="/admin/posts">
                    <Icon name="grid" className="size-4" />
                    Monitoring Semua Artikel
                </Button>
            }
        >
            <Seo title="Kelola Pengguna (Admin)" description="Panel admin BlogVerse Pro untuk mengelola pengguna dan memantau artikel mereka." />

            <section aria-label="Ringkasan pengguna" className="grid gap-4 sm:grid-cols-3">
                <StatCard label="Total Pengguna" value={numberFormat(stats.total)} icon="users" tone="blue" active={!filters.role} onClick={() => visit({ role: '' })} />
                <StatCard label="Admin" value={numberFormat(stats.admins)} icon="shield" tone="violet" active={filters.role === 'admin'} onClick={() => visit({ role: 'admin' })} />
                <StatCard label="Penulis" value={numberFormat(stats.members)} icon="pencil" tone="emerald" active={filters.role === 'member'} onClick={() => visit({ role: 'member' })} />
            </section>

            <form
                role="search"
                aria-label="Cari pengguna"
                onSubmit={(event) => {
                    event.preventDefault();
                    visit();
                }}
                className="mt-6 flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row"
            >
                <div className="relative flex-1">
                    <label htmlFor="user-search" className="sr-only">
                        Cari nama, username, atau email
                    </label>
                    <Icon name="search" className="pointer-events-none absolute left-3.5 top-1/2 size-5 -translate-y-1/2 text-slate-500" />
                    <input
                        id="user-search"
                        name="search"
                        type="search"
                        value={search}
                        onChange={(event) => setSearch(event.target.value)}
                        placeholder="Cari nama, username, atau email..."
                        className={`${fieldClass} pl-11 placeholder:text-slate-500`}
                    />
                </div>
                <div className="sm:w-44">
                    <label htmlFor="user-role" className="sr-only">
                        Filter peran
                    </label>
                    <select id="user-role" name="role" value={filters.role ?? ''} onChange={(event) => visit({ role: event.target.value })} className={fieldClass}>
                        <option value="">Semua peran</option>
                        <option value="admin">Admin</option>
                        <option value="member">Penulis</option>
                    </select>
                </div>
                <Button type="submit" variant="secondary">
                    <Icon name="search" className="size-4" />
                    Cari
                </Button>
            </form>

            <section aria-labelledby="tabel-pengguna" className="mt-6">
                <h2 id="tabel-pengguna" className="mb-3 text-lg font-bold text-slate-900">
                    Daftar Pengguna ({users.total})
                </h2>

                {users.data.length === 0 ? (
                    <EmptyState title="Pengguna tidak ditemukan" description="Coba kata kunci pencarian lain." />
                ) : (
                    <div role="region" aria-label="Tabel daftar pengguna" tabIndex={0} className="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
                        <table className="min-w-full divide-y divide-slate-200 text-left text-sm">
                            <caption className="sr-only">Tabel daftar pengguna</caption>
                            <thead className="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-700">
                                <tr>
                                    <th scope="col" className="px-4 py-3">
                                        Pengguna
                                    </th>
                                    <th scope="col" className="px-4 py-3">
                                        Email
                                    </th>
                                    <th scope="col" className="px-4 py-3">
                                        Peran
                                    </th>
                                    <th scope="col" className="px-4 py-3">
                                        Artikel
                                    </th>
                                    <th scope="col" className="px-4 py-3">
                                        Bergabung
                                    </th>
                                    <th scope="col" className="px-4 py-3 text-right">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {users.data.map((user) => (
                                    <tr key={user.id} className="hover:bg-slate-50">
                                        <td className="whitespace-nowrap px-4 py-3">
                                            <div className="flex items-center gap-3">
                                                <Avatar name={user.name} src={user.profile_photo_path ? `/storage/${user.profile_photo_path}` : null} size="size-9" />
                                                <div>
                                                    <p className="font-semibold text-slate-900">{user.name}</p>
                                                    <p className="text-xs text-slate-700">@{user.username}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 text-slate-800">{user.email}</td>
                                        <td className="whitespace-nowrap px-4 py-3">
                                            <span
                                                className={`inline-flex rounded-full px-2.5 py-1 text-xs font-semibold ${
                                                    user.role === 'admin' ? 'bg-violet-100 text-violet-800' : 'bg-emerald-100 text-emerald-800'
                                                }`}
                                            >
                                                {user.role === 'admin' ? 'Admin' : 'Penulis'}
                                            </span>
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 text-slate-800">
                                            <span className="font-semibold">{user.posts_count}</span> total · {user.published_posts_count} terbit
                                        </td>
                                        <td className="whitespace-nowrap px-4 py-3 text-slate-700">{formatDate(user.created_at)}</td>
                                        <td className="whitespace-nowrap px-4 py-3 text-right">
                                            <Link
                                                href={`/admin/users/${user.id}/posts`}
                                                className="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50"
                                                aria-label={`Lihat artikel milik ${user.name}`}
                                            >
                                                <Icon name="eye" className="size-4" />
                                                Lihat Artikel
                                            </Link>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                )}

                <div className="mt-6">
                    <Pagination paginator={users} label="Navigasi halaman pengguna" />
                </div>
            </section>
        </AppLayout>
    );
}
