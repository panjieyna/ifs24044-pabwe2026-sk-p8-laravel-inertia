import { Link } from '@inertiajs/react';
import { formatDate } from '../utils/format';
import { CategoryBadge, EmptyState, StatusBadge } from './Display';
import Icon from './Icon';

/**
 * Tabel monitoring artikel (read-only) untuk admin.
 */
export default function AdminPostTable({ posts, categories, showAuthor = true, caption }) {
    if (posts.data.length === 0) {
        return <EmptyState title="Tidak ada artikel" description="Belum ada artikel yang cocok dengan filter saat ini." />;
    }

    return (
        <div role="region" aria-label={caption} tabIndex={0} className="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table className="min-w-full divide-y divide-slate-200 text-left text-sm">
                <caption className="sr-only">{caption}</caption>
                <thead className="bg-slate-50 text-xs font-bold uppercase tracking-wider text-slate-700">
                    <tr>
                        <th scope="col" className="px-4 py-3">
                            Judul
                        </th>
                        {showAuthor && (
                            <th scope="col" className="px-4 py-3">
                                Penulis
                            </th>
                        )}
                        <th scope="col" className="px-4 py-3">
                            Kategori
                        </th>
                        <th scope="col" className="px-4 py-3">
                            Status
                        </th>
                        <th scope="col" className="px-4 py-3">
                            Tanggal
                        </th>
                        <th scope="col" className="px-4 py-3 text-right">
                            Aksi
                        </th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-slate-100">
                    {posts.data.map((post) => (
                        <tr key={post.id} className="hover:bg-slate-50">
                            <td className="max-w-xs px-4 py-3 font-semibold text-slate-900">
                                <span className="line-clamp-2">{post.title}</span>
                            </td>
                            {showAuthor && (
                                <td className="whitespace-nowrap px-4 py-3">
                                    <Link href={`/admin/users/${post.user_id}/posts`} className="font-medium text-blue-700 hover:underline">
                                        {post.user?.name ?? '-'}
                                    </Link>
                                </td>
                            )}
                            <td className="whitespace-nowrap px-4 py-3">
                                <CategoryBadge categories={categories} value={post.category} />
                            </td>
                            <td className="whitespace-nowrap px-4 py-3">
                                <StatusBadge published={post.is_published} />
                            </td>
                            <td className="whitespace-nowrap px-4 py-3 text-slate-700">{formatDate(post.published_at ?? post.created_at)}</td>
                            <td className="whitespace-nowrap px-4 py-3 text-right">
                                <Link
                                    href={post.is_published ? `/articles/${post.slug}` : `/dashboard/posts/${post.id}`}
                                    className="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold text-blue-700 hover:bg-blue-50"
                                    aria-label={`Lihat artikel ${post.title}`}
                                >
                                    <Icon name="eye" className="size-4" />
                                    Lihat
                                </Link>
                            </td>
                        </tr>
                    ))}
                </tbody>
            </table>
        </div>
    );
}
