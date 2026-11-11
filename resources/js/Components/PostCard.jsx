import { Link } from '@inertiajs/react';
import { formatDate } from '../utils/format';
import { Avatar, CategoryBadge } from './Display';
import Icon from './Icon';

const PLACEHOLDER_TONES = {
    Teknologi: 'from-blue-700 to-indigo-800',
    Pendidikan: 'from-emerald-700 to-teal-800',
    'Gaya Hidup': 'from-pink-700 to-rose-800',
    Bisnis: 'from-amber-600 to-orange-700',
    Opini: 'from-violet-700 to-purple-800',
    Umum: 'from-slate-600 to-slate-800',
};

export function CoverPlaceholder({ category, className = '' }) {
    return (
        <div className={`flex items-center justify-center bg-gradient-to-br ${PLACEHOLDER_TONES[category] ?? PLACEHOLDER_TONES.Umum} ${className}`} aria-hidden="true">
            <Icon name="bookOpen" className="size-12 text-white/80" strokeWidth={1.4} />
        </div>
    );
}

/**
 * Kartu artikel untuk galeri publik.
 */
export default function PostCard({ post, categories, headingLevel = 'h3', priority = false }) {
    const Heading = headingLevel;
    const href = `/articles/${post.slug}`;

    return (
        <article className="group flex flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg">
            <Link href={href} tabIndex={-1} aria-hidden="true" className="block aspect-[16/9] overflow-hidden bg-slate-200">
                {post.cover_url ? (
                    <img
                        src={post.cover_url}
                        alt=""
                        width="640"
                        height="360"
                        loading={priority ? 'eager' : 'lazy'}
                        fetchPriority={priority ? 'high' : 'auto'}
                        decoding="async"
                        className="size-full object-cover transition duration-300 group-hover:scale-105"
                    />
                ) : (
                    <CoverPlaceholder category={post.category} className="size-full" />
                )}
            </Link>

            <div className="flex flex-1 flex-col p-5">
                <div className="flex items-center justify-between gap-2">
                    <CategoryBadge categories={categories} value={post.category} />
                    <span className="inline-flex items-center gap-1 text-xs text-slate-700">
                        <Icon name="calendar" className="size-3.5" />
                        <time dateTime={post.published_at}>{formatDate(post.published_at)}</time>
                    </span>
                </div>

                <Heading className="mt-3 text-lg font-bold leading-snug text-slate-900">
                    <Link href={href} className="hover:text-blue-800 hover:underline">
                        {post.title}
                    </Link>
                </Heading>

                {post.excerpt && <p className="mt-2 line-clamp-3 text-sm leading-relaxed text-slate-700">{post.excerpt}</p>}

                <div className="mt-auto flex items-center gap-2.5 pt-5">
                    <Avatar name={post.user?.name} src={post.user?.profile_photo_path ? `/storage/${post.user.profile_photo_path}` : null} size="size-8" textSize="text-xs" />
                    <span className="text-sm font-medium text-slate-800">{post.user?.name ?? 'Penulis'}</span>
                </div>
            </div>
        </article>
    );
}
