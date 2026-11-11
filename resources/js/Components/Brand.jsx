import { Link } from '@inertiajs/react';

export function BrandMark({ className = 'size-9' }) {
    return (
        <svg className={className} viewBox="0 0 64 64" aria-hidden="true" focusable="false">
            <rect width="64" height="64" rx="14" fill="#1d4ed8" />
            <path
                d="M18 16h17c7 0 11 3.6 11 9 0 3.6-2 6.2-5 7.4 3.9 1 6.4 4 6.4 8.1 0 6-4.6 9.5-12 9.5H18zm8 6.4v7.3h7.6c3 0 4.6-1.3 4.6-3.7s-1.6-3.6-4.6-3.6zm0 13.4v7.8h8.4c3.2 0 5-1.4 5-3.9s-1.8-3.9-5-3.9z"
                fill="#fff"
            />
            <circle cx="50" cy="50" r="5" fill="#fbbf24" />
        </svg>
    );
}

export default function Brand({ href = '/', inverted = false }) {
    return (
        <Link href={href} className={`flex items-center gap-2.5 rounded-lg ${inverted ? 'text-white' : 'text-slate-900'}`}>
            <BrandMark />
            <span className="text-lg font-extrabold tracking-tight">
                BlogVerse <span className={inverted ? 'text-amber-300' : 'text-blue-700'}>Pro</span>
            </span>
        </Link>
    );
}
