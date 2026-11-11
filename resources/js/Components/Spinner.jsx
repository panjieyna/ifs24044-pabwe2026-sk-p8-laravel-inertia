export default function Spinner({ className = 'size-4' }) {
    return (
        <svg className={`${className} animate-spin`} viewBox="0 0 24 24" fill="none" aria-hidden="true" focusable="false">
            <circle className="opacity-30" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
            <path className="opacity-90" fill="currentColor" d="M4 12a8 8 0 0 1 8-8v4a4 4 0 0 0-4 4H4Z" />
        </svg>
    );
}
