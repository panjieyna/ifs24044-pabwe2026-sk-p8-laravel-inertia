import { Link, router } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import Button from '../../Components/Button';
import ConfirmDeleteModal from '../../Components/ConfirmDeleteModal';
import { Avatar, CategoryBadge, StatusBadge } from '../../Components/Display';
import Icon from '../../Components/Icon';
import { LazyMarkdownViewer } from '../../Components/LazyMarkdown';
import { CoverPlaceholder } from '../../Components/PostCard';
import PostFormModal from '../../Components/PostFormModal';
import Seo from '../../Components/Seo';
import Spinner from '../../Components/Spinner';
import AppLayout from '../../Layouts/AppLayout';
import { formatDateTime, readingTime } from '../../utils/format';

function InfoRow({ label, children }) {
    return (
        <div className="flex items-start justify-between gap-4 py-2.5">
            <dt className="text-sm text-slate-700">{label}</dt>
            <dd className="text-right text-sm font-semibold text-slate-900">{children}</dd>
        </div>
    );
}

export default function Show({ post, canEdit, categories }) {
    const [editing, setEditing] = useState(false);
    const [deleting, setDeleting] = useState(false);
    const [busy, setBusy] = useState(null); // 'toggle' | 'cover-upload' | 'cover-delete'
    const fileRef = useRef(null);

    useEffect(() => {
        const params = new URLSearchParams(window.location.search);
        if (canEdit && params.get('edit') === '1') {
            setEditing(true);
            window.history.replaceState(window.history.state, '', window.location.pathname);
        }
    }, [canEdit]);

    const request = (key, method, url, data = {}, options = {}) => {
        router[method](url, data, {
            preserveScroll: true,
            onStart: () => setBusy(key),
            onFinish: () => setBusy(null),
            ...options,
        });
    };

    const uploadCover = (event) => {
        const file = event.target.files?.[0];
        if (!file) return;
        request('cover-upload', 'post', `/dashboard/posts/${post.id}/cover`, { cover: file }, { forceFormData: true, onFinish: () => {
            setBusy(null);
            if (fileRef.current) fileRef.current.value = '';
        } });
    };

    return (
        <AppLayout
            title={post.title}
            subtitle={`${readingTime(post.content)} menit baca · ${post.is_published ? 'Dipublikasikan' : 'Draf'}`}
            back={{ href: canEdit ? '/dashboard/posts' : '/admin/posts', label: canEdit ? 'Kembali ke daftar artikel' : 'Kembali ke monitoring artikel' }}
            actions={
                canEdit ? (
                    <>
                        <Button variant="success" onClick={() => setEditing(true)}>
                            <Icon name="pencil" className="size-4" />
                            Ubah Edit
                        </Button>
                        <Button variant="secondary" onClick={() => request('toggle', 'patch', `/dashboard/posts/${post.id}/publish`)} loading={busy === 'toggle'}>
                            {busy !== 'toggle' && <Icon name={post.is_published ? 'eyeSlash' : 'globe'} className="size-4" />}
                            {post.is_published ? 'Jadikan Draf' : 'Publikasikan'}
                        </Button>
                        <Button variant="danger" onClick={() => setDeleting(true)}>
                            <Icon name="trash" className="size-4" />
                            Hapus
                        </Button>
                    </>
                ) : null
            }
        >
            <Seo title={post.title} description={post.excerpt} />

            {!canEdit && (
                <p className="mb-6 flex items-center gap-2 rounded-xl border border-amber-300 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">
                    <Icon name="shield" className="size-5" />
                    Mode baca saja (Admin) — artikel ini milik {post.user?.name}.
                </p>
            )}

            <div className="grid gap-6 lg:grid-cols-3">
                <article className="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm lg:col-span-2">
                    {post.cover_url ? (
                        <img
                            src={post.cover_url}
                            alt={`Foto sampul artikel ${post.title}`}
                            width="1200"
                            height="630"
                            fetchPriority="high"
                            decoding="async"
                            className="aspect-[1200/630] w-full object-cover"
                        />
                    ) : (
                        <CoverPlaceholder category={post.category} className="aspect-[1200/630] w-full" />
                    )}

                    <div className="p-5 sm:p-8">
                        <div className="flex flex-wrap items-center gap-2">
                            <CategoryBadge categories={categories} value={post.category} />
                            <StatusBadge published={post.is_published} />
                        </div>
                        {post.excerpt && <p className="mt-4 text-lg leading-relaxed text-slate-700">{post.excerpt}</p>}
                        <div className="mt-6 border-t border-slate-200 pt-6">
                            <LazyMarkdownViewer content={post.content} fallbackText={post.content} />
                        </div>
                    </div>
                </article>

                <div className="space-y-6">
                    <section aria-labelledby="info-artikel" className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                        <h2 id="info-artikel" className="text-base font-bold text-slate-900">
                            Informasi Artikel
                        </h2>
                        <dl className="mt-2 divide-y divide-slate-100">
                            <InfoRow label="Penulis">
                                <span className="inline-flex items-center gap-2">
                                    <Avatar name={post.user?.name} src={post.user?.profile_photo_path ? `/storage/${post.user.profile_photo_path}` : null} size="size-6" textSize="text-[10px]" />
                                    {post.user?.name}
                                </span>
                            </InfoRow>
                            <InfoRow label="Slug">
                                <span className="break-all font-mono text-xs">{post.slug}</span>
                            </InfoRow>
                            <InfoRow label="Dipublikasikan">{formatDateTime(post.published_at)}</InfoRow>
                            <InfoRow label="Dibuat">{formatDateTime(post.created_at)}</InfoRow>
                            <InfoRow label="Diperbarui">{formatDateTime(post.updated_at)}</InfoRow>
                        </dl>
                        {post.is_published && (
                            <Link
                                href={`/articles/${post.slug}`}
                                className="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-50 px-4 py-2.5 text-sm font-semibold text-blue-800 hover:bg-blue-100"
                            >
                                <Icon name="globe" className="size-4" />
                                Lihat di Blog Publik
                            </Link>
                        )}
                    </section>

                    {canEdit && (
                        <section aria-labelledby="kelola-sampul" className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
                            <h2 id="kelola-sampul" className="text-base font-bold text-slate-900">
                                Foto Sampul
                            </h2>
                            <p className="mt-1 text-sm text-slate-700">JPG, PNG, atau WEBP maksimal 5 MB. Gambar otomatis dioptimalkan.</p>
                            <div className="mt-4 flex flex-wrap gap-2">
                                <label
                                    htmlFor="cover-upload"
                                    className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800"
                                >
                                    {busy === 'cover-upload' ? <Spinner className="size-4" /> : <Icon name="photo" className="size-4" />}
                                    {busy === 'cover-upload' ? 'Mengunggah...' : post.cover_url ? 'Ganti Sampul' : 'Unggah Sampul'}
                                </label>
                                <input
                                    ref={fileRef}
                                    id="cover-upload"
                                    name="cover"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    className="sr-only"
                                    onChange={uploadCover}
                                    disabled={busy !== null}
                                />
                                {post.cover_url && (
                                    <Button variant="secondary" onClick={() => request('cover-delete', 'delete', `/dashboard/posts/${post.id}/cover`)} loading={busy === 'cover-delete'}>
                                        {busy !== 'cover-delete' && <Icon name="trash" className="size-4" />}
                                        Hapus Sampul
                                    </Button>
                                )}
                            </div>
                        </section>
                    )}
                </div>
            </div>

            {editing && <PostFormModal open mode="edit" post={post} categories={categories} onClose={() => setEditing(false)} />}
            {deleting && <ConfirmDeleteModal post={post} onClose={() => setDeleting(false)} />}
        </AppLayout>
    );
}
