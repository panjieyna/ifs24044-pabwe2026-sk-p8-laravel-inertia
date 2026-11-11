import { useForm } from '@inertiajs/react';
import { useId } from 'react';
import Button from './Button';
import { InputError, Label, SelectInput, TextArea, TextInput } from './Form';
import Icon from './Icon';
import { LazyMarkdownEditor } from './LazyMarkdown';
import Modal from './Modal';

/**
 * Modal full screen untuk menambah (mode "create") atau mengubah (mode "edit") artikel.
 */
export default function PostFormModal({ open, onClose, mode = 'create', post = null, categories = [] }) {
    const isEdit = mode === 'edit';
    const contentLabelId = useId();
    const form = useForm({
        title: post?.title ?? '',
        category: post?.category ?? 'Umum',
        excerpt: post?.excerpt ?? '',
        content: post?.content ?? '',
        is_published: post?.is_published ?? false,
        cover: null,
    });
    const { data, setData, errors, processing } = form;

    const submit = () => {
        if (isEdit) {
            form.transform(({ cover, ...rest }) => rest);
            form.put(`/dashboard/posts/${post.id}`, { preserveScroll: true, onSuccess: () => onClose() });
            return;
        }

        form.transform((values) => values);
        form.post('/dashboard/posts', { forceFormData: true, preserveScroll: true, onSuccess: () => onClose() });
    };

    return (
        <Modal
            open={open}
            onClose={onClose}
            closeDisabled={processing}
            theme={isEdit ? 'emerald' : 'indigo'}
            title={isEdit ? 'Ubah Artikel' : 'Tambah Artikel Baru'}
            description={isEdit ? 'Perbarui isi, kategori, atau status publikasi artikel Anda.' : 'Tulis artikel baru menggunakan editor Markdown.'}
            onSubmit={submit}
            footer={
                <>
                    <Button variant="secondary" onClick={onClose} disabled={processing}>
                        Batal
                    </Button>
                    <Button type="submit" variant={isEdit ? 'success' : 'primary'} loading={processing}>
                        {processing ? 'Menyimpan...' : isEdit ? 'Simpan Perubahan' : 'Simpan Artikel'}
                    </Button>
                </>
            }
        >
            <div className="mx-auto grid max-w-6xl gap-6 lg:grid-cols-3">
                <div className="space-y-5 lg:col-span-2">
                    <div>
                        <Label htmlFor="post-title" required>
                            Judul Artikel
                        </Label>
                        <TextInput
                            id="post-title"
                            name="title"
                            value={data.title}
                            onChange={(event) => setData('title', event.target.value)}
                            maxLength={255}
                            data-autofocus
                            placeholder="Contoh: Panduan Lengkap Belajar Laravel 13"
                            error={errors.title}
                        />
                    </div>

                    <div>
                        <p id={contentLabelId} className="mb-1.5 block text-sm font-semibold text-slate-800">
                            Isi Artikel (Markdown)
                            <span className="ml-0.5 text-red-700" aria-hidden="true">
                                *
                            </span>
                        </p>
                        <LazyMarkdownEditor value={data.content} onChange={(value) => setData('content', value)} labelledBy={contentLabelId} height="460px" />
                        <InputError message={errors.content} />
                    </div>
                </div>

                <div className="space-y-5">
                    <div>
                        <Label htmlFor="post-category" required>
                            Kategori
                        </Label>
                        <SelectInput id="post-category" name="category" value={data.category} onChange={(event) => setData('category', event.target.value)} error={errors.category}>
                            {categories.map((item) => (
                                <option key={item.value} value={item.value}>
                                    {item.label}
                                </option>
                            ))}
                        </SelectInput>
                    </div>

                    <div>
                        <Label htmlFor="post-excerpt">Ringkasan (Excerpt)</Label>
                        <TextArea
                            id="post-excerpt"
                            name="excerpt"
                            rows={4}
                            maxLength={500}
                            value={data.excerpt ?? ''}
                            onChange={(event) => setData('excerpt', event.target.value)}
                            placeholder="1–2 kalimat pemikat pembaca. Kosongkan untuk dibuat otomatis dari isi artikel."
                            error={errors.excerpt}
                        />
                    </div>

                    {!isEdit && (
                        <div>
                            <Label htmlFor="post-cover">Foto Sampul (opsional)</Label>
                            <input
                                id="post-cover"
                                name="cover"
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                onChange={(event) => setData('cover', event.target.files?.[0] ?? null)}
                                className="block w-full text-sm text-slate-800 file:mr-3 file:rounded-lg file:border-0 file:bg-indigo-100 file:px-3 file:py-2 file:font-semibold file:text-indigo-900 hover:file:bg-indigo-200"
                                aria-describedby="post-cover-hint"
                            />
                            <p id="post-cover-hint" className="mt-1.5 text-xs text-slate-700">
                                Format JPG, PNG, atau WEBP. Maksimal 5 MB.
                            </p>
                            <InputError message={errors.cover} />
                        </div>
                    )}

                    <fieldset className="rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <legend className="px-1 text-sm font-semibold text-slate-800">Status Publikasi</legend>
                        <div className="mt-1 flex items-start gap-3">
                            <input
                                id="post-is-published"
                                name="is_published"
                                type="checkbox"
                                checked={Boolean(data.is_published)}
                                onChange={(event) => setData('is_published', event.target.checked)}
                                className="mt-0.5 size-4 rounded border-slate-400"
                            />
                            <label htmlFor="post-is-published" className="text-sm text-slate-800">
                                <span className="flex items-center gap-1.5 font-semibold">
                                    <Icon name="globe" className="size-4" />
                                    Publikasikan ke umum
                                </span>
                                <span className="mt-0.5 block text-slate-700">Jika tidak dicentang, artikel disimpan sebagai draf.</span>
                            </label>
                        </div>
                        <InputError message={errors.is_published} />
                    </fieldset>
                </div>
            </div>
        </Modal>
    );
}
