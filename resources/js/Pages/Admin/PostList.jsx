import AdminPostTable from '../../Components/AdminPostTable';
import Button from '../../Components/Button';
import Icon from '../../Components/Icon';
import Pagination from '../../Components/Pagination';
import PostFilters from '../../Components/PostFilters';
import Seo from '../../Components/Seo';
import AppLayout from '../../Layouts/AppLayout';

export default function PostList({ posts, filters, categories }) {
    return (
        <AppLayout
            title="Semua Artikel"
            subtitle="Monitoring seluruh artikel di sistem (read-only)."
            actions={
                <Button variant="secondary" href="/admin/posts/export" external>
                    <Icon name="download" className="size-4" />
                    Ekspor Semua ke Excel
                </Button>
            }
        >
            <Seo title="Semua Artikel (Admin)" description="Panel admin BlogVerse Pro untuk memantau seluruh artikel sistem." />

            <PostFilters url="/admin/posts" filters={filters} categories={categories} idPrefix="admin-posts" />

            <section aria-labelledby="tabel-semua-artikel" className="mt-6">
                <h2 id="tabel-semua-artikel" className="mb-3 text-lg font-bold text-slate-900">
                    Daftar Artikel ({posts.total})
                </h2>
                <AdminPostTable posts={posts} categories={categories} caption="Tabel seluruh artikel sistem" />
                <div className="mt-6">
                    <Pagination paginator={posts} label="Navigasi halaman semua artikel" />
                </div>
            </section>
        </AppLayout>
    );
}
