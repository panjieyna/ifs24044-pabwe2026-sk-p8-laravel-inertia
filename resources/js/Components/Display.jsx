import { categoryMeta, initials } from '../utils/format';
import Icon from './Icon';

export function Avatar({ name, src, size = 'size-10', textSize = 'text-sm' }) {
    if (src) {
        return <img src={src} alt={`Foto profil ${name}`} width="40" height="40" className={`${size} shrink-0 rounded-full object-cover`} />;
    }

    return (
        <span
            className={`${size} ${textSize} inline-flex shrink-0 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-800`}
            aria-hidden="true"
        >
            {initials(name)}
        </span>
    );
}

export function CategoryBadge({ categories, value }) {
    const meta = categoryMeta(categories, value);

    return <span className={`inline-flex items-center rounded-full px-2.5 py-1 text-xs font-semibold ${meta.badgeClass}`}>{meta.value}</span>;
}

export function StatusBadge({ published }) {
    return published ? (
        <span className="inline-flex items-center gap-1 rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-800">
            <Icon name="globe" className="size-3.5" />
            Published
        </span>
    ) : (
        <span className="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-900">
            <Icon name="pencil" className="size-3.5" />
            Draft
        </span>
    );
}

const STAT_TONES = {
    blue: { wrap: 'border-blue-200 bg-blue-50', icon: 'bg-blue-700 text-white', value: 'text-blue-900' },
    emerald: { wrap: 'border-emerald-200 bg-emerald-50', icon: 'bg-emerald-700 text-white', value: 'text-emerald-900' },
    amber: { wrap: 'border-amber-200 bg-amber-50', icon: 'bg-amber-600 text-white', value: 'text-amber-900' },
    violet: { wrap: 'border-violet-200 bg-violet-50', icon: 'bg-violet-700 text-white', value: 'text-violet-900' },
};

export function StatCard({ label, value, hint, icon, tone = 'blue', active = false, onClick }) {
    const colors = STAT_TONES[tone] ?? STAT_TONES.blue;
    const content = (
        <>
            <span className={`inline-flex size-11 shrink-0 items-center justify-center rounded-xl ${colors.icon}`}>
                <Icon name={icon} className="size-6" />
            </span>
            <span className="text-left">
                <span className="block text-sm font-medium text-slate-700">{label}</span>
                <span className={`block text-2xl font-bold ${colors.value}`}>{value}</span>
                {hint && <span className="block text-xs text-slate-700">{hint}</span>}
            </span>
        </>
    );
    const className = `flex w-full items-center gap-4 rounded-2xl border p-4 transition ${colors.wrap} ${active ? 'ring-2 ring-blue-700 ring-offset-2' : ''}`;

    if (onClick) {
        return (
            <button type="button" onClick={onClick} aria-pressed={active} className={`${className} hover:shadow-md`}>
                {content}
            </button>
        );
    }

    return <div className={className}>{content}</div>;
}

export function EmptyState({ title, description, action }) {
    return (
        <div className="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-12 text-center">
            <Icon name="document" className="mx-auto size-10 text-slate-500" />
            <p className="mt-3 text-base font-semibold text-slate-900">{title}</p>
            {description && <p className="mt-1 text-sm text-slate-700">{description}</p>}
            {action && <div className="mt-5 flex justify-center">{action}</div>}
        </div>
    );
}
