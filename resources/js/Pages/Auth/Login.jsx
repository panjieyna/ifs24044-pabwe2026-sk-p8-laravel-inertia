import { Link, useForm } from '@inertiajs/react';
import AuthCard from '../../Components/AuthCard';
import Button from '../../Components/Button';
import { Label, TextInput } from '../../Components/Form';
import Seo from '../../Components/Seo';
import PublicLayout from '../../Layouts/PublicLayout';

export default function Login() {
    const { data, setData, post, processing, errors, reset } = useForm({
        login: '',
        password: '',
        remember: false,
    });

    const submit = (event) => {
        event.preventDefault();
        post('/login', { onFinish: () => reset('password') });
    };

    return (
        <PublicLayout>
            <Seo title="Masuk" description="Masuk ke BlogVerse Pro untuk mengelola artikel Anda sebagai penulis atau admin." />

            <AuthCard
                title="Masuk ke BlogVerse Pro"
                subtitle="Kelola artikel, publikasikan tulisan, dan pantau statistik Anda."
                icon="login"
                footer={
                    <>
                        Belum punya akun?{' '}
                        <Link href="/register" className="font-semibold text-blue-700 underline-offset-2 hover:underline">
                            Daftar sebagai penulis
                        </Link>
                    </>
                }
            >
                <form onSubmit={submit} className="space-y-5" noValidate>
                    <div>
                        <Label htmlFor="login-username" required>
                            Username atau Email
                        </Label>
                        <TextInput
                            id="login-username"
                            name="login"
                            type="text"
                            value={data.login}
                            onChange={(event) => setData('login', event.target.value)}
                            autoComplete="username"
                            autoFocus
                            required
                            placeholder="contoh: member"
                            error={errors.login}
                        />
                    </div>

                    <div>
                        <Label htmlFor="login-password" required>
                            Password
                        </Label>
                        <TextInput
                            id="login-password"
                            name="password"
                            type="password"
                            value={data.password}
                            onChange={(event) => setData('password', event.target.value)}
                            autoComplete="current-password"
                            required
                            placeholder="Masukkan password"
                            error={errors.password}
                        />
                    </div>

                    <div className="flex items-center gap-2">
                        <input
                            id="login-remember"
                            name="remember"
                            type="checkbox"
                            checked={data.remember}
                            onChange={(event) => setData('remember', event.target.checked)}
                            className="size-4 rounded border-slate-400 text-blue-700"
                        />
                        <label htmlFor="login-remember" className="text-sm text-slate-800">
                            Ingat saya
                        </label>
                    </div>

                    <Button id="login-submit" type="submit" size="lg" loading={processing} className="w-full">
                        {processing ? 'Memproses...' : 'Masuk'}
                    </Button>
                </form>
            </AuthCard>
        </PublicLayout>
    );
}
