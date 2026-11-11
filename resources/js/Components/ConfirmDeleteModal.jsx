import { router } from '@inertiajs/react';
import { useState } from 'react';
import Button from './Button';
import Icon from './Icon';
import Modal from './Modal';

/**
 * Dialog konfirmasi hapus artikel (dengan spinner saat proses).
 */
export default function ConfirmDeleteModal({ post, onClose }) {
    const [processing, setProcessing] = useState(false);

    const confirm = () => {
        router.delete(`/dashboard/posts/${post.id}`, {
            preserveScroll: true,
            onStart: () => setProcessing(true),
            onFinish: () => setProcessing(false),
            onSuccess: () => onClose(),
        });
    };

    return (
        <Modal
            open={Boolean(post)}
            onClose={onClose}
            closeDisabled={processing}
            fullScreen={false}
            theme="red"
            title="Hapus Artikel?"
            footer={
                <>
                    <Button variant="secondary" onClick={onClose} disabled={processing}>
                        Batal
                    </Button>
                    <Button variant="danger" onClick={confirm} loading={processing}>
                        {!processing && <Icon name="trash" className="size-4" />}
                        {processing ? 'Menghapus...' : 'Ya, Hapus'}
                    </Button>
                </>
            }
        >
            <p className="text-sm leading-relaxed text-slate-800">
                Artikel <strong className="font-semibold">“{post?.title}”</strong> beserta foto sampulnya akan dihapus permanen. Tindakan ini tidak
                dapat dibatalkan.
            </p>
        </Modal>
    );
}
