import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import AiGeneratorModal from '../../Components/AiGeneratorModal';
import Button from '../../Components/Button';
import ConfirmDeleteModal from '../../Components/ConfirmDeleteModal';
import { CategoryBadge, EmptyState, StatCard, StatusBadge } from '../../Components/Display';
import Icon from '../../Components/Icon';
import ImportModal from '../../Components/ImportModal';
import KebabMenu from '../../Components/KebabMenu';
import Pagination from '../../Components/Pagination';
import PostFilters from '../../Components/PostFilters';
import PostFormModal from '../../Components/PostFormModal';
import { CoverPlaceholder } from '../../Components/PostCard';
import Seo from '../../Components/Seo';
import Spinner from '../../Components/Spinner';
import AppLayout from '../../Layouts/AppLayout';
import { formatDate, numberFormat } from '../../utils/format';

export default function Index({ posts, stats, filters, categories, aiMaxPosts }) {
    const [modal, setModal] = useState(null); // 'create' | 'ai' | 'import'
    const [deleting, setDeleting] = useState(null);
    const [togglingId, setTogglingId] = useState(null);

    const filterStatus = (status) => {
        const params = { search: filters.search, category: filters.category, status: filters.status === status ? '' : status };
        Object.keys(params).forEach((key) => !params[key] && delete params[key]);
        router.get('/dashboard/posts', params, { preserveScroll: true, preserveState: true });
    };

    const togglePublish = (post) => {
        router.patch(`/dashboard/posts/${post.id}/publish`, {}, {
            preserveScroll: true,
            onStart: () => setTogglingId(post.id),
            onFinish: () => setTogglingId(null),
        });
    };

    return (
        <AppLayout
            title="Artikel Saya"
            subtitle="Kelola tulisan Anda: buat, ubah, publikasikan, dan pantau statistik artikel."
            actions={
                <>
                    <Button onClick={() => setModal('create')}>
                        <Icon name="plus" className="size-4" />
                        Tambah Artikel
                    </Button>
                    <Button variant="violet" onClick={() => setModal('ai')}>
                        <Icon name="sparkles" className="size-4" />
                        AI Generator
                    </Button>
                    <Button variant="sky" onClick={() => setModal('import')}>
                        <Icon name="upload" className="size-4" />
                        Impor Excel
                    </Button>
                    <Button variant="secondary" href="/dashboard/posts/export" external>
                        <Icon name="download" className="size-4" />
                        Ekspor Excel
                    </Button>
                </>
            }
        >
            <Seo title="Artikel Saya" description="Dashboard penulis BlogVerse Pro untuk mengelola artikel, draf, dan publikasi." />

            <section aria-label="Ringkasan statistik artikel" className="grid gap-4 sm:grid-cols-3">
                <StatCard label="Total Artikel" value={numberFormat(stats.total)} hint="Seluruh tulisan Anda" icon="document" tone="blue" active={!filters.status} onClick={() => filterStatus('')} />
                <StatCard
                    label="Total Selesai"
                    value={numberFormat(stats.published)}
                    hint="Sudah dipublikasikan"
                    icon="globe"
                    tone="emerald"
                    active={filters.status === 'published'}
                    onClick={() => filterStatus('published')}
                />
                <StatCard
                    label="Belum Selesai"
                    value={numberFormat(stats.draft)}
                    hint="Masih berstatus draf"
                    icon="pencil"
                    tone="amber"
                    active={filters.status === 'draft'}
                    onClick={() => filterStatus('draft')}
                />
            </section>

            <div className="mt-6">
                <PostFilters url="/dashboard/posts" filters={filters} categories={categories} idPrefix="dashboard" />
            </div>

            <section aria-labelledby="daftar-artikel-saya" className="mt-6">
                <h2 id="daftar-artikel-saya" className="sr-only">
                    Daftar artikel saya
                </h2>

                {posts.data.length === 0 ? (
                    <EmptyState
                        title={filters.search || filters.status || filters.category ? 'Tidak ada artikel yang cocok dengan filter' : 'Belum ada artikel'}
                        description="Mulai tulis artikel pertama Anda atau gunakan AI Generator untuk inspirasi."
                        action={
                            <Button onClick={() => setModal('create')}>
                                <Icon name="plus" className="size-4" />
                                Tulis Artikel
                            </Button>
                        }
                    />
                ) : (
                    <ul className="divide-y divide-slate-200 rounded-2xl border border-slate-200 bg-white shadow-sm">
                        {posts.data.map((post) => (
                            <li key={post.id} className="flex items-center gap-4 px-4 py-4 sm:px-5">
                                <div className="hidden size-14 shrink-0 overflow-hidden rounded-xl bg-slate-200 sm:block">
                                    {post.cover_url ? (
                                        <img src={post.cover_url} alt="" width="56" height="56" loading="lazy" decoding="async" className="size-full object-cover" />
                                    ) : (
                                        <CoverPlaceholder category={post.category} className="size-full [&_svg]:size-6" />
                                    )}
                                </div>

                                <div className="min-w-0 flex-1">
                                    <Link href={`/dashboard/posts/${post.id}`} className="line-clamp-2 font-semibold text-slate-900 hover:text-blue-800 hover:underline">
                                        {post.title}
                                    </Link>
                                    <div className="mt-1.5 flex flex-wrap items-center gap-2 text-xs text-slate-700">
                                        <CategoryBadge categories={categories} value={post.category} />
                                        <StatusBadge published={post.is_published} />
                                        <span className="inline-flex items-center gap-1">
                                            <Icon name="calendar" className="size-3.5" />
                                            {post.is_published ? `Terbit ${formatDate(post.published_at)}` : `Diperbarui ${formatDate(post.updated_at)}`}
                                        </span>
                                    </div>
                                </div>

                                <div className="flex shrink-0 items-center gap-1">
                                    <button
                                        type="button"
                                        onClick={() => togglePublish(post)}
                                        disabled={togglingId === post.id}
                                        className={`hidden items-center gap-1.5 rounded-lg px-3 py-2 text-xs font-semibold sm:inline-flex ${
                                            post.is_published ? 'text-amber-900 hover:bg-amber-50' : 'text-emerald-800 hover:bg-emerald-50'
                                        }`}
                                    >
                                        {togglingId === post.id ? <Spinner className="size-4" /> : <Icon name={post.is_published ? 'eyeSlash' : 'globe'} className="size-4" />}
                                        {post.is_published ? 'Jadikan Draf' : 'Publikasikan'}
                                    </button>
                                    <KebabMenu
                                        label={`Aksi untuk artikel ${post.title}`}
                                        items={[
                                            { key: 'detail', label: 'Detail', icon: 'eye', tone: 'primary', href: `/dashboard/posts/${post.id}` },
                                            { key: 'edit', label: 'Ubah Edit', icon: 'pencil', tone: 'primary', href: `/dashboard/posts/${post.id}?edit=1` },
                                            {
                                                key: 'toggle',
                                                label: post.is_published ? 'Jadikan Draf' : 'Publikasikan',
                                                icon: post.is_published ? 'eyeSlash' : 'globe',
                                                tone: 'primary',
                                                onClick: () => togglePublish(post),
                                            },
                                            { key: 'delete', label: 'Hapus', icon: 'trash', tone: 'danger', onClick: () => setDeleting(post) },
                                        ]}
                                    />
                                </div>
                            </li>
                        ))}
                    </ul>
                )}

                <div className="mt-6">
                    <Pagination paginator={posts} label="Navigasi halaman artikel saya" />
                </div>
            </section>

            {modal === 'create' && <PostFormModal open mode="create" categories={categories} onClose={() => setModal(null)} />}
            {modal === 'ai' && <AiGeneratorModal open categories={categories} maxPosts={aiMaxPosts} onClose={() => setModal(null)} />}
            {modal === 'import' && <ImportModal open onClose={() => setModal(null)} />}
            {deleting && <ConfirmDeleteModal post={deleting} onClose={() => setDeleting(null)} />}
        </AppLayout>
    );
}
