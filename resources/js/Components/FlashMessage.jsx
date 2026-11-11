import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import Icon from './Icon';

/**
 * Notifikasi flash (success / error) dari session Laravel.
 */
export default function FlashMessage() {
    const { flash } = usePage().props;
    const [dismissed, setDismissed] = useState({ success: null, error: null });

    useEffect(() => {
        setDismissed({ success: null, error: null });
    }, [flash?.success, flash?.error]);

    const items = [
        flash?.success && dismissed.success !== flash.success
            ? { type: 'success', message: flash.success, className: 'border-emerald-300 bg-emerald-50 text-emerald-900', icon: 'checkCircle' }
            : null,
        flash?.error && dismissed.error !== flash.error
            ? { type: 'error', message: flash.error, className: 'border-red-300 bg-red-50 text-red-900', icon: 'warning' }
            : null,
    ].filter(Boolean);

    if (items.length === 0) return null;

    return (
        <div className="space-y-3" aria-live="polite">
            {items.map((item) => (
                <div
                    key={item.type}
                    role={item.type === 'error' ? 'alert' : 'status'}
                    className={`animate-fade-in flex items-start gap-3 rounded-xl border px-4 py-3 text-sm font-medium ${item.className}`}
                >
                    <Icon name={item.icon} className="mt-0.5 size-5 shrink-0" />
                    <p className="flex-1">{item.message}</p>
                    <button
                        type="button"
                        onClick={() => setDismissed((prev) => ({ ...prev, [item.type]: item.message }))}
                        className="-m-1 rounded-lg p-1.5 hover:bg-black/5"
                        aria-label="Tutup notifikasi"
                    >
                        <Icon name="x" className="size-4" />
                    </button>
                </div>
            ))}
        </div>
    );
}
