import Icon from './Icon';

/**
 * Kerangka kartu untuk halaman Login & Register.
 */
export default function AuthCard({ title, subtitle, icon, children, footer }) {
    return (
        <section className="mx-auto w-full max-w-md px-4 py-12 sm:py-16">
            <div className="rounded-3xl border border-slate-200 bg-white p-6 shadow-xl sm:p-8">
                <div className="mb-6 text-center">
                    <span className="mx-auto inline-flex size-12 items-center justify-center rounded-2xl bg-blue-700 text-white">
                        <Icon name={icon} className="size-6" />
                    </span>
                    <h1 className="mt-4 text-2xl font-bold tracking-tight text-slate-900">{title}</h1>
                    {subtitle && <p className="mt-1.5 text-sm text-slate-700">{subtitle}</p>}
                </div>
                {children}
            </div>
            {footer && <div className="mt-6 text-center text-sm text-slate-700">{footer}</div>}
        </section>
    );
}
