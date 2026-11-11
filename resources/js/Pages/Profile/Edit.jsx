import { router, useForm } from '@inertiajs/react';
import { useRef, useState } from 'react';
import Button from '../../Components/Button';
import { Avatar } from '../../Components/Display';
import { InputError, Label, TextInput } from '../../Components/Form';
import Icon from '../../Components/Icon';
import Seo from '../../Components/Seo';
import Spinner from '../../Components/Spinner';
import AppLayout from '../../Layouts/AppLayout';

function Card({ id, title, description, children }) {
    return (
        <section aria-labelledby={id} className="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <h2 id={id} className="text-lg font-bold text-slate-900">
                {title}
            </h2>
            {description && <p className="mt-1 text-sm text-slate-700">{description}</p>}
            <div className="mt-5">{children}</div>
        </section>
    );
}

export default function Edit({ profile }) {
    const fileRef = useRef(null);
    const [photoBusy, setPhotoBusy] = useState(null);
    const [photoError, setPhotoError] = useState(null);

    const identity = useForm({ name: profile.name, username: profile.username, email: profile.email });
    const password = useForm({ current_password: '', password: '', password_confirmation: '' });

    const uploadPhoto = (event) => {
        const file = event.target.files?.[0];
        if (!file) return;
        router.post(
            '/profile/photo',
            { photo: file },
            {
                forceFormData: true,
                preserveScroll: true,
                onStart: () => {
                    setPhotoBusy('upload');
                    setPhotoError(null);
                },
                onError: (errors) => setPhotoError(errors.photo),
                onFinish: () => {
                    setPhotoBusy(null);
                    if (fileRef.current) fileRef.current.value = '';
                },
            },
        );
    };

    const deletePhoto = () => {
        router.delete('/profile/photo', {
            preserveScroll: true,
            onStart: () => setPhotoBusy('delete'),
            onFinish: () => setPhotoBusy(null),
        });
    };

    return (
        <AppLayout title="Profil Saya" subtitle="Kelola identitas penulis, foto profil, dan keamanan akun Anda.">
            <Seo title="Profil Saya" description="Kelola profil penulis BlogVerse Pro: identitas, foto profil, dan password." />

            <div className="grid gap-6 lg:grid-cols-3">
                <div className="space-y-6">
                    <Card id="foto-profil" title="Foto Profil" description="JPG, PNG, atau WEBP maksimal 2 MB.">
                        <div className="flex flex-col items-center text-center">
                            <Avatar name={profile.name} src={profile.profile_photo_url} size="size-28" textSize="text-3xl" />
                            <p className="mt-3 text-base font-semibold text-slate-900">{profile.name}</p>
                            <p className="text-sm text-slate-700">
                                @{profile.username} · {profile.role === 'admin' ? 'Admin' : 'Penulis'}
                            </p>
                            <div className="mt-5 flex flex-wrap justify-center gap-2">
                                <label
                                    htmlFor="profile-photo"
                                    className="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white hover:bg-blue-800"
                                >
                                    {photoBusy === 'upload' ? <Spinner className="size-4" /> : <Icon name="upload" className="size-4" />}
                                    {photoBusy === 'upload' ? 'Mengunggah...' : 'Unggah Foto'}
                                </label>
                                <input
                                    ref={fileRef}
                                    id="profile-photo"
                                    name="photo"
                                    type="file"
                                    accept="image/jpeg,image/png,image/webp"
                                    className="sr-only"
                                    onChange={uploadPhoto}
                                    disabled={photoBusy !== null}
                                />
                                {profile.profile_photo_url && (
                                    <Button variant="secondary" onClick={deletePhoto} loading={photoBusy === 'delete'}>
                                        {photoBusy !== 'delete' && <Icon name="trash" className="size-4" />}
                                        Hapus
                                    </Button>
                                )}
                            </div>
                            <InputError message={photoError} />
                        </div>
                    </Card>

                    <Card id="statistik-profil" title="Ringkasan Akun">
                        <dl className="space-y-3 text-sm">
                            <div className="flex justify-between">
                                <dt className="text-slate-700">Total artikel</dt>
                                <dd className="font-semibold text-slate-900">{profile.posts_count}</dd>
                            </div>
                            <div className="flex justify-between">
                                <dt className="text-slate-700">Bergabung</dt>
                                <dd className="font-semibold text-slate-900">{profile.joined_at ?? '-'}</dd>
                            </div>
                        </dl>
                    </Card>
                </div>

                <div className="space-y-6 lg:col-span-2">
                    <Card id="identitas-profil" title="Identitas Penulis" description="Nama dan username ditampilkan pada setiap artikel yang Anda publikasikan.">
                        <form
                            onSubmit={(event) => {
                                event.preventDefault();
                                identity.patch('/profile', { preserveScroll: true });
                            }}
                            className="space-y-4"
                            noValidate
                        >
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <Label htmlFor="profile-name" required>
                                        Nama Lengkap
                                    </Label>
                                    <TextInput
                                        id="profile-name"
                                        name="name"
                                        autoComplete="name"
                                        value={identity.data.name}
                                        onChange={(event) => identity.setData('name', event.target.value)}
                                        error={identity.errors.name}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="profile-username" required>
                                        Username
                                    </Label>
                                    <TextInput
                                        id="profile-username"
                                        name="username"
                                        autoComplete="username"
                                        value={identity.data.username}
                                        onChange={(event) => identity.setData('username', event.target.value)}
                                        error={identity.errors.username}
                                    />
                                </div>
                            </div>
                            <div>
                                <Label htmlFor="profile-email" required>
                                    Email
                                </Label>
                                <TextInput
                                    id="profile-email"
                                    name="email"
                                    type="email"
                                    autoComplete="email"
                                    value={identity.data.email}
                                    onChange={(event) => identity.setData('email', event.target.value)}
                                    error={identity.errors.email}
                                />
                            </div>
                            <div className="flex justify-end">
                                <Button type="submit" loading={identity.processing}>
                                    {identity.processing ? 'Menyimpan...' : 'Simpan Identitas'}
                                </Button>
                            </div>
                        </form>
                    </Card>

                    <Card id="keamanan-akun" title="Ubah Password" description="Gunakan password minimal 8 karakter yang sulit ditebak.">
                        <form
                            onSubmit={(event) => {
                                event.preventDefault();
                                password.put('/profile/password', { preserveScroll: true, onSuccess: () => password.reset() });
                            }}
                            className="space-y-4"
                            noValidate
                        >
                            <div>
                                <Label htmlFor="profile-current-password" required>
                                    Password Saat Ini
                                </Label>
                                <TextInput
                                    id="profile-current-password"
                                    name="current_password"
                                    type="password"
                                    autoComplete="current-password"
                                    value={password.data.current_password}
                                    onChange={(event) => password.setData('current_password', event.target.value)}
                                    error={password.errors.current_password}
                                />
                            </div>
                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <Label htmlFor="profile-new-password" required>
                                        Password Baru
                                    </Label>
                                    <TextInput
                                        id="profile-new-password"
                                        name="password"
                                        type="password"
                                        autoComplete="new-password"
                                        value={password.data.password}
                                        onChange={(event) => password.setData('password', event.target.value)}
                                        error={password.errors.password}
                                    />
                                </div>
                                <div>
                                    <Label htmlFor="profile-confirm-password" required>
                                        Konfirmasi Password Baru
                                    </Label>
                                    <TextInput
                                        id="profile-confirm-password"
                                        name="password_confirmation"
                                        type="password"
                                        autoComplete="new-password"
                                        value={password.data.password_confirmation}
                                        onChange={(event) => password.setData('password_confirmation', event.target.value)}
                                        error={password.errors.password_confirmation}
                                    />
                                </div>
                            </div>
                            <div className="flex justify-end">
                                <Button type="submit" variant="success" loading={password.processing}>
                                    {password.processing ? 'Menyimpan...' : 'Ubah Password'}
                                </Button>
                            </div>
                        </form>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
