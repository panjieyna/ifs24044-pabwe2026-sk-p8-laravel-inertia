import AdminPostTable from '../../Components/AdminPostTable';
import Button from '../../Components/Button';
import { StatCard } from '../../Components/Display';
import Icon from '../../Components/Icon';
import Pagination from '../../Components/Pagination';
import PostFilters from '../../Components/PostFilters';
import Seo from '../../Components/Seo';
import AppLayout from '../../Layouts/AppLayout';
import { numberFormat } from '../../utils/format';

export default function UserPostList({ targetUser, posts, stats, filters, categories }) {
    return (
        <AppLayout
            title={`Artikel ${targetUser.name}`}
            subtitle={`@${targetUser.username} · ${targetUser.email} · ${targetUser.role === 'admin' ? 'Admin' : 'Penulis'}`}
            back={{ href: '/admin/users', label: 'Kembali ke daftar pengguna' }}
            actions={
                <Button variant="secondary" href={`/admin/users/${targetUser.id}/posts/export`} external>
                    <Icon name="download" className="size-4" />
                    Ekspor Artikel User
                </Button>
            }
        >
            <Seo title={`Artikel ${targetUser.name} (Admin)`} description={`Monitoring artikel milik ${targetUser.name} di BlogVerse Pro (read-only).`} />

            <section aria-label="Ringkasan artikel pengguna" className="grid gap-4 sm:grid-cols-3">
                <StatCard label="Total Artikel" value={numberFormat(stats.total)} icon="document" tone="blue" />
                <StatCard label="Total Selesai" value={numberFormat(stats.published)} hint="Dipublikasikan" icon="globe" tone="emerald" />
                <StatCard label="Belum Selesai" value={numberFormat(stats.draft)} hint="Draf" icon="pencil" tone="amber" />
            </section>

            <div className="mt-6">
                <PostFilters url={`/admin/users/${targetUser.id}/posts`} filters={filters} categories={categories} idPrefix="user-posts" />
            </div>

            <section aria-labelledby="tabel-artikel-user" className="mt-6">
                <h2 id="tabel-artikel-user" className="mb-3 text-lg font-bold text-slate-900">
                    Daftar Artikel ({posts.total})
                </h2>
                <AdminPostTable posts={posts} categories={categories} showAuthor={false} caption={`Tabel artikel milik ${targetUser.name}`} />
                <div className="mt-6">
                    <Pagination paginator={posts} label="Navigasi halaman artikel pengguna" />
                </div>
            </section>
        </AppLayout>
    );
}
