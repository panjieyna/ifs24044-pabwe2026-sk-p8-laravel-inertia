import { lazy, Suspense } from 'react';
import Spinner from './Spinner';

const MarkdownEditorImpl = lazy(() => import('./MarkdownEditor'));
const MarkdownViewerImpl = lazy(() => import('./MarkdownViewer'));

function Loading({ label, minHeight }) {
    return (
        <div className="flex items-center justify-center gap-2 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-700" style={{ minHeight }}>
            <Spinner className="size-4 text-blue-700" />
            {label}
        </div>
    );
}

/**
 * Editor Markdown (TOAST UI) yang dimuat terpisah agar halaman awal tetap ringan.
 */
export function LazyMarkdownEditor(props) {
    return (
        <Suspense fallback={<Loading label="Memuat editor..." minHeight={props.height ?? '420px'} />}>
            <MarkdownEditorImpl {...props} />
        </Suspense>
    );
}

/**
 * Viewer Markdown (TOAST UI) yang dimuat terpisah.
 */
export function LazyMarkdownViewer({ content, fallbackText = '' }) {
    return (
        <Suspense
            fallback={
                <div className="article-content whitespace-pre-line text-[1.0625rem] leading-[1.8] text-slate-800">{fallbackText}</div>
            }
        >
            <MarkdownViewerImpl content={content} />
        </Suspense>
    );
}
