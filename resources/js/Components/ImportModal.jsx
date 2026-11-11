import { useForm } from '@inertiajs/react';
import Button from './Button';
import { InputError, Label } from './Form';
import Icon from './Icon';
import Modal from './Modal';

/**
 * Modal impor artikel dari berkas Excel (.xlsx/.xls/.csv).
 */
export default function ImportModal({ open, onClose }) {
    const { data, setData, post, processing, errors } = useForm({ importFile: null });

    const submit = () => {
        post('/dashboard/posts/import', { forceFormData: true, preserveScroll: true, onSuccess: () => onClose() });
    };

    return (
        <Modal
            open={open}
            onClose={onClose}
            closeDisabled={processing}
            theme="sky"
            title="Impor Artikel dari Excel"
            description="Tambahkan banyak artikel sekaligus dari berkas spreadsheet."
            onSubmit={submit}
            footer={
                <>
                    <Button variant="secondary" onClick={onClose} disabled={processing}>
                        Batal
                    </Button>
                    <Button type="submit" variant="sky" loading={processing} disabled={!data.importFile}>
                        {!processing && <Icon name="upload" className="size-4" />}
                        {processing ? 'Mengimpor...' : 'Impor Sekarang'}
                    </Button>
                </>
            }
        >
            <div className="mx-auto max-w-2xl space-y-6">
                <div>
                    <Label htmlFor="import-file" required>
                        Berkas Excel / CSV
                    </Label>
                    <input
                        id="import-file"
                        name="importFile"
                        type="file"
                        accept=".xlsx,.xls,.csv"
                        data-autofocus
                        onChange={(event) => setData('importFile', event.target.files?.[0] ?? null)}
                        className="block w-full rounded-xl border border-dashed border-sky-400 bg-sky-50 p-4 text-sm text-slate-800 file:mr-3 file:rounded-lg file:border-0 file:bg-sky-800 file:px-3 file:py-2 file:font-semibold file:text-white"
                        aria-describedby="import-file-hint"
                    />
                    <p id="import-file-hint" className="mt-1.5 text-xs text-slate-700">
                        Format .xlsx, .xls, atau .csv. Maksimal 10 MB.
                    </p>
                    <InputError message={errors.importFile} />
                </div>

                <div className="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-800">
                    <p className="font-semibold">Format kolom (baris pertama = header):</p>
                    <ol className="mt-2 list-inside list-decimal space-y-1">
                        <li>A: No (diabaikan)</li>
                        <li>B: Judul (wajib)</li>
                        <li>C: Kategori (Teknologi, Pendidikan, Gaya Hidup, Bisnis, Opini, Umum)</li>
                        <li>D: Ringkasan</li>
                        <li>E: Konten (Markdown)</li>
                        <li>F: Status (Published / Draft)</li>
                    </ol>
                    <p className="mt-3">
                        Tip: unduh hasil{' '}
                        <a href="/dashboard/posts/export" className="font-semibold text-blue-700 underline">
                            Ekspor Excel
                        </a>{' '}
                        sebagai template.
                    </p>
                </div>
            </div>
        </Modal>
    );
}
