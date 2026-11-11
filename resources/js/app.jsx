import '../css/app.css';
import { createInertiaApp } from '@inertiajs/react';
import { createRoot } from 'react-dom/client';

const appName = import.meta.env.VITE_APP_NAME || 'BlogVerse Pro';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    // Setiap halaman dimuat sebagai chunk terpisah (code splitting) agar
    // halaman publik tidak ikut mengunduh kode dashboard/admin.
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.jsx');
        const page = pages[`./Pages/${name}.jsx`];

        if (!page) {
            throw new Error(`Halaman ${name} tidak ditemukan.`);
        }

        return page();
    },
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
    progress: {
        color: '#f59e0b',
        showSpinner: false,
    },
});
