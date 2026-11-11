import { router, useForm, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import Button from './Button';
import { Label, SelectInput, TextArea, TextInput } from './Form';
import Icon from './Icon';
import Modal from './Modal';

/**
 * Modal AI Generator: rancang draf artikel instan dengan OpenAI lalu simpan sebagai draf.
 */
export default function AiGeneratorModal({ open, onClose, categories = [], maxPosts = 5 }) {
    const { flash } = usePage().props;
    const form = useForm({ prompt: '', count: 1, category: '' });
    const [items, setItems] = useState([]);
    const [selected, setSelected] = useState([]);
    const [saving, setSaving] = useState(false);
    const [requested, setRequested] = useState(false);

    useEffect(() => {
        if (Array.isArray(flash?.aiGeneratedItems) && flash.aiGeneratedItems.length > 0) {
            setItems(flash.aiGeneratedItems);
            setSelected(flash.aiGeneratedItems.map((_, index) => index));
        }
    }, [flash?.aiGeneratedItems]);

    const generate = () => {
        setRequested(true);
        form.post('/dashboard/posts/ai-generate', { preserveScroll: true, preserveState: true });
    };

    const save = () => {
        router.post(
            '/dashboard/posts/ai-save',
            { items: items.filter((_, index) => selected.includes(index)) },
            {
                preserveScroll: true,
                onStart: () => setSaving(true),
                onFinish: () => setSaving(false),
                onSuccess: () => onClose(),
            },
        );
    };

    const toggle = (index) => setSelected((prev) => (prev.includes(index) ? prev.filter((value) => value !== index) : [...prev, index]));
    const busy = form.processing || saving;

    return (
        <Modal
            open={open}
            onClose={onClose}
            closeDisabled={busy}
            theme="violet"
            title="AI Generator Artikel"
            description="Jelaskan topik yang ingin ditulis, lalu biarkan AI merancang draf artikel untuk Anda."
            onSubmit={generate}
            footer={
                <>
                    <Button variant="secondary" onClick={onClose} disabled={busy}>
                        Batal
                    </Button>
                    {items.length > 0 && (
                        <Button variant="success" onClick={save} loading={saving} disabled={selected.length === 0 || form.processing}>
                            {saving ? 'Menyimpan...' : `Simpan ${selected.length} Draf`}
                        </Button>
                    )}
                    <Button type="submit" variant="violet" loading={form.processing} disabled={saving}>
                        {!form.processing && <Icon name="sparkles" className="size-4" />}
                        {form.processing ? 'Merancang...' : items.length > 0 ? 'Generate Ulang' : 'Generate'}
                    </Button>
                </>
            }
        >
            <div className="mx-auto grid max-w-6xl gap-6 lg:grid-cols-5">
                <div className="space-y-5 lg:col-span-2">
                    <div>
                        <Label htmlFor="ai-prompt" required>
                            Instruksi / Topik Artikel
                        </Label>
                        <TextArea
                            id="ai-prompt"
                            name="prompt"
                            rows={6}
                            maxLength={1000}
                            data-autofocus
                            value={form.data.prompt}
                            onChange={(event) => form.setData('prompt', event.target.value)}
                            placeholder="Contoh: Tips belajar pemrograman web untuk mahasiswa semester awal"
                            error={form.errors.prompt}
                        />
                    </div>

                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                        <div>
                            <Label htmlFor="ai-count" required>
                                Jumlah Artikel
                            </Label>
                            <TextInput
                                id="ai-count"
                                name="count"
                                type="number"
                                inputMode="numeric"
                                min={1}
                                max={maxPosts}
                                step={1}
                                value={form.data.count}
                                onChange={(event) => form.setData('count', event.target.value === '' ? '' : Math.min(maxPosts, Math.max(1, Number(event.target.value))))}
                                aria-describedby="ai-count-hint"
                                error={form.errors.count}
                            />
                            <p id="ai-count-hint" className="mt-1.5 text-xs text-slate-700">
                                Antara 1 sampai {maxPosts} artikel.
                            </p>
                        </div>
                        <div>
                            <Label htmlFor="ai-category">Kategori</Label>
                            <SelectInput id="ai-category" name="category" value={form.data.category} onChange={(event) => form.setData('category', event.target.value)} error={form.errors.category}>
                                <option value="">Biarkan AI memilih</option>
                                {categories.map((item) => (
                                    <option key={item.value} value={item.value}>
                                        {item.label}
                                    </option>
                                ))}
                            </SelectInput>
                        </div>
                    </div>

                    {requested && flash?.error && !form.processing && (
                        <p role="alert" className="rounded-xl border border-red-300 bg-red-50 px-4 py-3 text-sm font-medium text-red-900">
                            {flash.error}
                        </p>
                    )}
                </div>

                <section aria-labelledby="ai-hasil" className="lg:col-span-3">
                    <h3 id="ai-hasil" className="text-base font-semibold text-slate-900">
                        Hasil Rancangan AI
                    </h3>
                    {items.length === 0 ? (
                        <div className="mt-3 rounded-2xl border border-dashed border-violet-300 bg-violet-50 px-6 py-12 text-center text-sm text-violet-900">
                            <Icon name="sparkles" className="mx-auto size-8" />
                            <p className="mt-2">Draf artikel hasil AI akan muncul di sini. Pilih yang ingin disimpan sebagai draf.</p>
                        </div>
                    ) : (
                        <ul className="mt-3 space-y-3">
                            {items.map((item, index) => (
                                <li key={`${item.title}-${index}`} className="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                                    <div className="flex items-start gap-3">
                                        <input
                                            id={`ai-item-${index}`}
                                            type="checkbox"
                                            checked={selected.includes(index)}
                                            onChange={() => toggle(index)}
                                            className="mt-1 size-4 rounded border-slate-400"
                                        />
                                        <label htmlFor={`ai-item-${index}`} className="min-w-0 flex-1">
                                            <span className="block font-semibold text-slate-900">{item.title}</span>
                                            <span className="mt-1 inline-block rounded-full bg-violet-100 px-2 py-0.5 text-xs font-semibold text-violet-900">{item.category}</span>
                                            {item.excerpt && <span className="mt-2 block text-sm text-slate-700">{item.excerpt}</span>}
                                            <span className="mt-2 line-clamp-3 block whitespace-pre-line text-xs text-slate-700">{item.content}</span>
                                        </label>
                                    </div>
                                </li>
                            ))}
                        </ul>
                    )}
                </section>
            </div>
        </Modal>
    );
}
