import { Link, useForm } from '@inertiajs/react';
import AuthCard from '../../Components/AuthCard';
import Button from '../../Components/Button';
import { Label, TextInput } from '../../Components/Form';
import Seo from '../../Components/Seo';
import PublicLayout from '../../Layouts/PublicLayout';

const FIELDS = [
    { key: 'name', label: 'Nama Lengkap', type: 'text', autoComplete: 'name', placeholder: 'Nama Anda' },
    { key: 'username', label: 'Username', type: 'text', autoComplete: 'username', placeholder: 'huruf, angka, - atau _' },
    { key: 'email', label: 'Email', type: 'email', autoComplete: 'email', placeholder: 'nama@email.com' },
    { key: 'password', label: 'Password', type: 'password', autoComplete: 'new-password', placeholder: 'Minimal 8 karakter' },
    { key: 'password_confirmation', label: 'Konfirmasi Password', type: 'password', autoComplete: 'new-password', placeholder: 'Ulangi password' },
];

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        username: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit = (event) => {
        event.preventDefault();
        post('/register', { onFinish: () => reset('password', 'password_confirmation') });
    };

    return (
        <PublicLayout>
            <Seo title="Daftar Penulis" description="Daftar gratis sebagai penulis BlogVerse Pro dan mulai publikasikan artikel Anda." />

            <AuthCard
                title="Daftar sebagai Penulis"
                subtitle="Buat akun gratis dan mulai bagikan tulisan Anda ke pembaca."
                icon="userPlus"
                footer={
                    <>
                        Sudah punya akun?{' '}
                        <Link href="/login" className="font-semibold text-blue-700 underline-offset-2 hover:underline">
                            Masuk di sini
                        </Link>
                    </>
                }
            >
                <form onSubmit={submit} className="space-y-4" noValidate>
                    {FIELDS.map((field, index) => (
                        <div key={field.key}>
                            <Label htmlFor={`register-${field.key}`} required>
                                {field.label}
                            </Label>
                            <TextInput
                                id={`register-${field.key}`}
                                name={field.key}
                                type={field.type}
                                value={data[field.key]}
                                onChange={(event) => setData(field.key, event.target.value)}
                                autoComplete={field.autoComplete}
                                autoFocus={index === 0}
                                required
                                placeholder={field.placeholder}
                                error={errors[field.key]}
                            />
                        </div>
                    ))}

                    <Button id="register-submit" type="submit" size="lg" loading={processing} className="w-full">
                        {processing ? 'Mendaftarkan...' : 'Buat Akun'}
                    </Button>
                </form>
            </AuthCard>
        </PublicLayout>
    );
}
