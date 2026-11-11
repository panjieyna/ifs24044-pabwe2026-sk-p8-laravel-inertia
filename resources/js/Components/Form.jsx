export function Label({ htmlFor, children, required = false, className = '' }) {
    return (
        <label htmlFor={htmlFor} className={`mb-1.5 block text-sm font-semibold text-slate-800 ${className}`}>
            {children}
            {required && (
                <span className="ml-0.5 text-red-700" aria-hidden="true">
                    *
                </span>
            )}
        </label>
    );
}

export function InputError({ id, message }) {
    if (!message) return null;

    return (
        <p id={id} className="mt-1.5 text-sm font-medium text-red-700">
            {message}
        </p>
    );
}

const fieldClass =
    'block w-full rounded-xl border border-slate-300 bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm placeholder:text-slate-500 focus:border-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-600/30 aria-[invalid=true]:border-red-600';

export function TextInput({ id, error, className = '', ...props }) {
    return (
        <>
            <input
                id={id}
                name={props.name ?? id}
                className={`${fieldClass} ${className}`}
                aria-invalid={error ? 'true' : undefined}
                aria-describedby={error ? `${id}-error` : props['aria-describedby']}
                {...props}
            />
            <InputError id={`${id}-error`} message={error} />
        </>
    );
}

export function TextArea({ id, error, className = '', rows = 3, ...props }) {
    return (
        <>
            <textarea
                id={id}
                name={props.name ?? id}
                rows={rows}
                className={`${fieldClass} ${className}`}
                aria-invalid={error ? 'true' : undefined}
                aria-describedby={error ? `${id}-error` : undefined}
                {...props}
            />
            <InputError id={`${id}-error`} message={error} />
        </>
    );
}

export function SelectInput({ id, error, className = '', children, ...props }) {
    return (
        <>
            <select
                id={id}
                name={props.name ?? id}
                className={`${fieldClass} ${className}`}
                aria-invalid={error ? 'true' : undefined}
                aria-describedby={error ? `${id}-error` : undefined}
                {...props}
            >
                {children}
            </select>
            <InputError id={`${id}-error`} message={error} />
        </>
    );
}
