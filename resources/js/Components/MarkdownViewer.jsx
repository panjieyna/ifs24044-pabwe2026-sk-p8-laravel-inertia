import Viewer from '@toast-ui/editor/viewer';
import '@toast-ui/editor/dist/toastui-editor-viewer.css';
import { useEffect, useRef } from 'react';

/**
 * TOAST UI Viewer — merender konten Markdown artikel (bundle viewer saja, lebih ringan).
 */
export default function MarkdownViewer({ content = '' }) {
    const elementRef = useRef(null);
    const viewerRef = useRef(null);

    useEffect(() => {
        if (!elementRef.current) return undefined;

        viewerRef.current = new Viewer({
            el: elementRef.current,
            initialValue: content || '',
            usageStatistics: false,
        });

        return () => {
            viewerRef.current?.destroy();
            viewerRef.current = null;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    useEffect(() => {
        viewerRef.current?.setMarkdown(content || '');
    }, [content]);

    return <div ref={elementRef} className="article-content" />;
}
