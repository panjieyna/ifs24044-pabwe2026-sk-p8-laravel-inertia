import { useEffect, useId, useRef } from 'react';
import Icon from './Icon';

/**
 * Tema warna modal: header & footer memiliki latar berbeda yang kontras dengan isi modal.
 */
const THEMES = {
    indigo: { header: 'bg-indigo-700 text-white', footer: 'bg-indigo-50 border-indigo-200', body: 'bg-white' },
    emerald: { header: 'bg-emerald-700 text-white', footer: 'bg-emerald-50 border-emerald-200', body: 'bg-white' },
    violet: { header: 'bg-violet-700 text-white', footer: 'bg-violet-50 border-violet-200', body: 'bg-white' },
    sky: { header: 'bg-sky-800 text-white', footer: 'bg-sky-50 border-sky-200', body: 'bg-white' },
    red: { header: 'bg-red-700 text-white', footer: 'bg-red-50 border-red-200', body: 'bg-white' },
};

const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

export default function Modal({
    open,
    onClose,
    title,
    description,
    theme = 'indigo',
    fullScreen = true,
    onSubmit,
    footer,
    children,
    closeDisabled = false,
}) {
    const titleId = useId();
    const descriptionId = useId();
    const panelRef = useRef(null);
    const colors = THEMES[theme] ?? THEMES.indigo;

    useEffect(() => {
        if (!open) return undefined;

        const previouslyFocused = document.activeElement;
        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';

        const focusTimer = setTimeout(() => {
            const target = panelRef.current?.querySelector('[data-autofocus]') ?? panelRef.current?.querySelector(FOCUSABLE);
            target?.focus();
        }, 30);

        const handleKeyDown = (event) => {
            if (event.key === 'Escape' && !closeDisabled) {
                event.stopPropagation();
                onClose?.();
            }

            if (event.key === 'Tab' && panelRef.current) {
                const focusable = Array.from(panelRef.current.querySelectorAll(FOCUSABLE));
                if (focusable.length === 0) return;
                const first = focusable[0];
                const last = focusable[focusable.length - 1];
                if (event.shiftKey && document.activeElement === first) {
                    event.preventDefault();
                    last.focus();
                } else if (!event.shiftKey && document.activeElement === last) {
                    event.preventDefault();
                    first.focus();
                }
            }
        };

        document.addEventListener('keydown', handleKeyDown);

        return () => {
            clearTimeout(focusTimer);
            document.removeEventListener('keydown', handleKeyDown);
            document.body.style.overflow = previousOverflow;
            previouslyFocused?.focus?.();
        };
    }, [open, onClose, closeDisabled]);

    if (!open) return null;

    const Wrapper = onSubmit ? 'form' : 'div';
    const wrapperProps = onSubmit
        ? {
              onSubmit: (event) => {
                  event.preventDefault();
                  onSubmit(event);
              },
              noValidate: true,
          }
        : {};

    return (
        <div className={`fixed inset-0 z-50 flex ${fullScreen ? '' : 'items-center justify-center bg-slate-900/60 p-4'}`}>
            <div
                ref={panelRef}
                role="dialog"
                aria-modal="true"
                aria-labelledby={titleId}
                aria-describedby={description ? descriptionId : undefined}
                className={`animate-fade-in flex flex-col overflow-hidden shadow-2xl ${
                    fullScreen ? 'h-full w-full' : 'max-h-[90vh] w-full max-w-lg rounded-2xl'
                } ${colors.body}`}
            >
                <Wrapper className="flex min-h-0 flex-1 flex-col" {...wrapperProps}>
                    <header className={`flex items-start justify-between gap-4 px-5 py-4 sm:px-8 ${colors.header}`}>
                        <div>
                            <h2 id={titleId} className="text-lg font-semibold sm:text-xl">
                                {title}
                            </h2>
                            {description && (
                                <p id={descriptionId} className="mt-1 text-sm text-white/90">
                                    {description}
                                </p>
                            )}
                        </div>
                        <button
                            type="button"
                            onClick={onClose}
                            disabled={closeDisabled}
                            className="rounded-lg p-2 text-white hover:bg-white/15 disabled:opacity-60"
                            aria-label="Tutup dialog"
                        >
                            <Icon name="x" className="size-5" />
                        </button>
                    </header>

                    <div className="min-h-0 flex-1 overflow-y-auto px-5 py-6 sm:px-8">{children}</div>

                    {footer && (
                        <footer className={`flex flex-wrap items-center justify-end gap-3 border-t px-5 py-4 sm:px-8 ${colors.footer}`}>
                            {footer}
                        </footer>
                    )}
                </Wrapper>
            </div>
        </div>
    );
}
