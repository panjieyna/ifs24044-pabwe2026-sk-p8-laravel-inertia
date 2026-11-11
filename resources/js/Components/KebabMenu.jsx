import { Link } from '@inertiajs/react';
import { useEffect, useId, useRef, useState } from 'react';
import Icon from './Icon';

const TONES = {
    primary: 'text-blue-700 hover:bg-blue-50',
    danger: 'text-red-700 hover:bg-red-50',
};

/**
 * Menu tindakan baris (tombol titik tiga vertikal ⋮).
 * items: [{ key, label, icon, tone: 'primary' | 'danger', href?, onClick? }]
 */
export default function KebabMenu({ label, items = [] }) {
    const [open, setOpen] = useState(false);
    const containerRef = useRef(null);
    const buttonRef = useRef(null);
    const menuId = useId();

    useEffect(() => {
        if (!open) return undefined;

        const handlePointer = (event) => {
            if (containerRef.current && !containerRef.current.contains(event.target)) {
                setOpen(false);
            }
        };
        const handleKey = (event) => {
            if (event.key === 'Escape') {
                setOpen(false);
                buttonRef.current?.focus();
            }
        };

        document.addEventListener('mousedown', handlePointer);
        document.addEventListener('keydown', handleKey);

        return () => {
            document.removeEventListener('mousedown', handlePointer);
            document.removeEventListener('keydown', handleKey);
        };
    }, [open]);

    return (
        <div ref={containerRef} className="relative">
            <button
                ref={buttonRef}
                type="button"
                onClick={() => setOpen((value) => !value)}
                aria-expanded={open}
                aria-controls={menuId}
                aria-label={label}
                className="rounded-lg p-2 text-slate-700 hover:bg-slate-100"
            >
                <Icon name="dotsVertical" className="size-5" />
            </button>

            {open && (
                <ul
                    id={menuId}
                    className="animate-fade-in absolute right-0 z-30 mt-1 w-44 overflow-hidden rounded-xl border border-slate-200 bg-white py-1 shadow-lg"
                >
                    {items.map((item) => {
                        const className = `flex w-full items-center gap-2.5 px-3.5 py-2.5 text-left text-sm font-medium ${TONES[item.tone] ?? TONES.primary}`;
                        const content = (
                            <>
                                <Icon name={item.icon} className="size-4" />
                                {item.label}
                            </>
                        );

                        return (
                            <li key={item.key}>
                                {item.href ? (
                                    <Link href={item.href} className={className} onClick={() => setOpen(false)}>
                                        {content}
                                    </Link>
                                ) : (
                                    <button
                                        type="button"
                                        className={className}
                                        onClick={() => {
                                            setOpen(false);
                                            item.onClick?.();
                                        }}
                                    >
                                        {content}
                                    </button>
                                )}
                            </li>
                        );
                    })}
                </ul>
            )}
        </div>
    );
}
