import { Link, usePage } from '@inertiajs/react';
import { Avatar, CategoryBadge } from '../../Components/Display';
import Icon from '../../Components/Icon';
import { LazyMarkdownViewer } from '../../Components/LazyMarkdown';
import PostCard from '../../Components/PostCard';
import Seo from '../../Components/Seo';
import PublicLayout from '../../Layouts/PublicLayout';
import { categoryMeta, formatDate, readingTime } from '../../utils/format';

function CommunityCard({ user }) {
    return (
        <section aria-labelledby="ajakan-komunitas" className="mt-12 rounded-3xl bg-blue-800 p-6 text-white sm:p-8">
            <h2 id="ajakan-komunitas" className="text-xl font-bold sm:text-2xl">
                Punya gagasan yang ingin dibagikan?
            </h2>
            <p className="mt-2 max-w-2xl text-blue-50">
                Bergabunglah dengan komunitas penulis BlogVerse Pro. Tulis artikel dengan editor Markdown, unggah foto sampul, dan publikasikan
                karya Anda kepada pembaca.
            </p>
            <div className="mt-6 flex flex-wrap gap-3">
                {user ? (
                    <Link
                        href="/dashboard/posts"
                        className="inline-flex items-center gap-2 rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-300"
                    >
                        <Icon name="pencil" className="size-4" />
                        Tulis Artikel di Dashboard
                    </Link>
                ) : (
                    <>
                        <Link
                            href="/login"
                            className="inline-flex items-center gap-2 rounded-xl bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-900 hover:bg-amber-300"
                        >
                            <Icon name="login" className="size-4" />
                            Halaman Login
                        </Link>
                        <Link
                            href="/register"
                            className="inline-flex items-center gap-2 rounded-xl border border-white/50 px-4 py-2.5 text-sm font-semibold text-white hover:bg-white/10"
                        >
                            <Icon name="userPlus" className="size-4" />
                            Halaman Register
                        </Link>
                    </>
                )}
            </div>
        </section>
    );
}

export default function PostShow({ post, related, categories }) {
    const { auth } = usePage().props;
    const meta = categoryMeta(categories, post.category);
    const author = post.user;

    return (
        <PublicLayout>
            <Seo title={post.title} description={post.excerpt} />

            <article className="mx-auto max-w-3xl px-4 pt-8 sm:px-6 sm:pt-12">
                <nav aria-label="Breadcrumb" className="text-sm text-slate-700">
                    <ol className="flex flex-wrap items-center gap-1.5">
                        <li>
                            <Link href="/" className="hover:text-blue-800 hover:underline">
                                Beranda
                            </Link>
                        </li>
                        <li aria-hidden="true">/</li>
                        <li>
                            <Link href="/articles" className="hover:text-blue-800 hover:underline">
                                Artikel
                            </Link>
                        </li>
                        <li aria-hidden="true">/</li>
                        <li>
                            <Link href={`/articles?category=${encodeURIComponent(post.category)}`} className="hover:text-blue-800 hover:underline">
                                {meta.value}
                            </Link>
                        </li>
                    </ol>
                </nav>

                <header className="mt-6">
                    <CategoryBadge categories={categories} value={post.category} />
                    <h1 className="mt-4 text-3xl font-extrabold leading-tight tracking-tight text-slate-900 sm:text-4xl">{post.title}</h1>
                    {post.excerpt && <p className="mt-4 text-lg leading-relaxed text-slate-700">{post.excerpt}</p>}

                    <div className="mt-6 flex flex-wrap items-center gap-x-5 gap-y-3 border-y border-slate-200 py-4 text-sm text-slate-700">
                        <span className="flex items-center gap-2.5">
                            <Avatar name={author?.name} src={author?.profile_photo_path ? `/storage/${author.profile_photo_path}` : null} size="size-10" />
                            <span>
                                <span className="block font-semibold text-slate-900">{author?.name ?? 'Penulis'}</span>
                                <span className="block">@{author?.username}</span>
                            </span>
                        </span>
                        <span className="inline-flex items-center gap-1.5">
                            <Icon name="calendar" className="size-4" />
                            <time dateTime={post.published_at}>{formatDate(post.published_at)}</time>
                        </span>
                        <span className="inline-flex items-center gap-1.5">
                            <Icon name="clock" className="size-4" />
                            {readingTime(post.content)} menit baca
                        </span>
                    </div>
                </header>

                {post.cover_url && (
                    <figure className="mt-8 overflow-hidden rounded-2xl bg-slate-200">
                        <img
                            src={post.cover_url}
                            alt={`Foto sampul artikel ${post.title}`}
                            width="1200"
                            height="630"
                            fetchPriority="high"
                            decoding="async"
                            className="aspect-[1200/630] w-full object-cover"
                        />
                    </figure>
                )}

                <div className="mt-8">
                    <LazyMarkdownViewer content={post.content} fallbackText={post.content} />
                </div>

                <CommunityCard user={auth?.user} />
            </article>

            {related.length > 0 && (
                <section aria-labelledby="artikel-terkait" className="mx-auto mt-14 max-w-6xl px-4 sm:px-6">
                    <h2 id="artikel-terkait" className="text-2xl font-bold tracking-tight text-slate-900">
                        Artikel Terkait
                    </h2>
                    <div className="mt-5 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        {related.map((item) => (
                            <PostCard key={item.id} post={item} categories={categories} />
                        ))}
                    </div>
                </section>
            )}
        </PublicLayout>
    );
}
