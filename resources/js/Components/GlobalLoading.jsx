import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import Spinner from './Spinner';

/**
 * Floating pill "Memproses tindakan..." yang tampil selama request Inertia berlangsung.
 * (Progress bar di atas layar disediakan oleh opsi `progress` Inertia.)
 */
export default function GlobalLoading() {
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        let timer = null;

        const removeStart = router.on('start', () => {
            clearTimeout(timer);
            timer = setTimeout(() => setVisible(true), 150);
        });

        const removeFinish = router.on('finish', () => {
            clearTimeout(timer);
            setVisible(false);
        });

        return () => {
            clearTimeout(timer);
            removeStart();
            removeFinish();
        };
    }, []);

    if (!visible) return null;

    return (
        <div
            role="status"
            aria-live="polite"
            className="animate-slide-up pointer-events-none fixed bottom-6 left-1/2 z-[70] flex -translate-x-1/2 items-center gap-2 rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-lg"
        >
            <Spinner className="size-4 text-amber-400" />
            Memproses tindakan...
        </div>
    );
}
