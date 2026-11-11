import { Link } from '@inertiajs/react';
import Spinner from './Spinner';

const VARIANTS = {
    primary: 'bg-blue-700 text-white hover:bg-blue-800 focus-visible:outline-blue-700',
    secondary: 'border border-slate-300 bg-white text-slate-800 hover:bg-slate-100',
    danger: 'bg-red-700 text-white hover:bg-red-800',
    success: 'bg-emerald-700 text-white hover:bg-emerald-800',
    violet: 'bg-violet-700 text-white hover:bg-violet-800',
    sky: 'bg-sky-800 text-white hover:bg-sky-900',
    ghost: 'text-slate-700 hover:bg-slate-100',
};

const SIZES = {
    sm: 'px-3 py-2 text-sm',
    md: 'px-4 py-2.5 text-sm',
    lg: 'px-5 py-3 text-base',
};

export default function Button({
    variant = 'primary',
    size = 'md',
    loading = false,
    href,
    external = false,
    className = '',
    children,
    disabled,
    type = 'button',
    ...props
}) {
    const classes = `inline-flex items-center justify-center gap-2 rounded-xl font-semibold transition-colors disabled:opacity-70 ${VARIANTS[variant] ?? VARIANTS.primary} ${SIZES[size] ?? SIZES.md} ${className}`;

    if (href && external) {
        return (
            <a href={href} className={classes} {...props}>
                {children}
            </a>
        );
    }

    if (href) {
        return (
            <Link href={href} className={classes} {...props}>
                {children}
            </Link>
        );
    }

    return (
        <button type={type} className={classes} disabled={disabled || loading} aria-busy={loading || undefined} {...props}>
            {loading && <Spinner className="size-4" />}
            {children}
        </button>
    );
}
