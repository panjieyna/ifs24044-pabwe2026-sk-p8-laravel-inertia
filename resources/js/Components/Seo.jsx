import { Head } from '@inertiajs/react';

const DEFAULT_DESCRIPTION =
    'BlogVerse Pro — platform publikasi artikel tentang teknologi, pendidikan, gaya hidup, bisnis, dan opini.';

/**
 * Judul halaman + meta description yang dikelola Inertia (head-key = "description").
 */
export default function Seo({ title, description }) {
    const content = String(description || DEFAULT_DESCRIPTION)
        .replace(/\s+/g, ' ')
        .trim()
        .slice(0, 160);

    return (
        <Head title={title}>
            <meta head-key="description" name="description" content={content} />
        </Head>
    );
}
