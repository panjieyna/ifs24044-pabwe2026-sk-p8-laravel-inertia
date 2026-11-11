import Editor from '@toast-ui/editor';
import '@toast-ui/editor/dist/toastui-editor.css';
import { useEffect, useRef } from 'react';

/**
 * Perbaikan aksesibilitas markup internal TOAST UI (atribut ARIA tidak valid, tombol tanpa nama, dll.).
 */
function improveAccessibility(root, labelledBy) {
    root.querySelectorAll('[aria-role]').forEach((node) => node.removeAttribute('aria-role'));
    root.querySelectorAll('.toastui-editor-tabs').forEach((node) => node.setAttribute('role', 'tablist'));
    root.querySelectorAll('.toastui-editor-tabs .tab-item').forEach((node) => node.setAttribute('role', 'tab'));
    root.querySelectorAll('button.more').forEach((node) => node.getAttribute('aria-label') || node.setAttribute('aria-label', 'Alat lainnya'));
    root.querySelectorAll('.toastui-editor-pseudo-clipboard').forEach((node) => {
        node.setAttribute('aria-label', 'Papan klip editor');
        node.setAttribute('tabindex', '-1');
    });
    root.querySelectorAll('[contenteditable="true"]').forEach((node) => {
        node.setAttribute('role', 'textbox');
        node.setAttribute('aria-multiline', 'true');
        if (labelledBy) node.setAttribute('aria-labelledby', labelledBy);
    });
}

/**
 * TOAST UI Editor (mode Markdown) — dimuat secara lazy hanya ketika modal form dibuka.
 */
export default function MarkdownEditor({ value = '', onChange, height = '420px', labelledBy, placeholder = 'Tulis isi artikel dalam format Markdown...' }) {
    const elementRef = useRef(null);
    const editorRef = useRef(null);
    const onChangeRef = useRef(onChange);

    onChangeRef.current = onChange;

    useEffect(() => {
        if (!elementRef.current) return undefined;

        const editor = new Editor({
            el: elementRef.current,
            height,
            initialEditType: 'markdown',
            previewStyle: 'tab',
            initialValue: value || '',
            placeholder,
            usageStatistics: false,
            hideModeSwitch: false,
            toolbarItems: [
                ['heading', 'bold', 'italic', 'strike'],
                ['hr', 'quote'],
                ['ul', 'ol', 'task'],
                ['table', 'link'],
                ['code', 'codeblock'],
            ],
            events: {
                change: () => onChangeRef.current?.(editor.getMarkdown()),
            },
        });

        const root = elementRef.current;
        improveAccessibility(root, labelledBy);

        // Toolbar/tab dapat dirender ulang oleh editor (mis. saat ganti mode), jadi terapkan ulang.
        const observer = new MutationObserver(() => improveAccessibility(root, labelledBy));
        observer.observe(root, { childList: true, subtree: true });

        editorRef.current = editor;

        return () => {
            observer.disconnect();
            editor.destroy();
            editorRef.current = null;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    useEffect(() => {
        const editor = editorRef.current;
        if (editor && value !== editor.getMarkdown()) {
            editor.setMarkdown(value || '', false);
        }
    }, [value]);

    return <div ref={elementRef} className="markdown-editor" />;
}
