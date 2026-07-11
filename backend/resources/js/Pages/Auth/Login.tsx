import Checkbox from '@/Components/Checkbox';
import InputError from '@/Components/InputError';
import { LogoMark, LogoWordmark } from '@/Components/site/Logo';
import { Head, Link, useForm } from '@inertiajs/react';
import { Eye, EyeOff, Lock, Mail } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

function GoogleButton({ label = 'Continue with Google' }: { label?: string }) {
    return (
        <a
            href="/auth/google"
            className="flex w-full items-center justify-center gap-3 rounded-xl border border-gray-300 bg-white px-4 py-3 text-sm font-semibold text-gray-700 shadow-sm transition hover:bg-gray-50 hover:shadow-md active:scale-[0.98]"
        >
            <svg width="20" height="20" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.35-8.16 2.35-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                <path fill="none" d="M0 0h48v48H0z"/>
            </svg>
            {label}
        </a>
    );
}

function Divider() {
    return (
        <div className="my-5 flex items-center gap-3">
            <div className="h-px flex-1 bg-gray-200" />
            <span className="text-xs font-medium text-gray-400 uppercase tracking-wide">or</span>
            <div className="h-px flex-1 bg-gray-200" />
        </div>
    );
}

export default function Login({ status, canResetPassword }: { status?: string; canResetPassword: boolean }) {
    const [showPassword, setShowPassword] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false as boolean,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), { onFinish: () => reset('password') });
    };

    return (
        <>
            <Head title="Log in · RentCeylon" />

            <div className="flex min-h-screen">
                {/* ── Left panel — hero visual ── */}
                <div
                    className="relative hidden w-[45%] flex-col justify-between overflow-hidden lg:flex"
                    style={{ backgroundColor: '#0c1e40' }}
                >
                    {/* gradient overlay */}
                    <div
                        className="absolute inset-0"
                        style={{
                            background: 'linear-gradient(135deg, #0c1e40 0%, #1a3a6e 50%, #0f2850 100%)',
                        }}
                    />
                    {/* decorative circles */}
                    <div className="absolute -top-24 -left-24 h-96 w-96 rounded-full opacity-10" style={{ backgroundColor: '#3868AE' }} />
                    <div className="absolute bottom-20 -right-16 h-72 w-72 rounded-full opacity-10" style={{ backgroundColor: '#C6900F' }} />
                    <div className="absolute top-1/2 left-1/2 h-64 w-64 -translate-x-1/2 -translate-y-1/2 rounded-full opacity-5" style={{ backgroundColor: '#AFCBEF' }} />

                    {/* Content */}
                    <div className="relative z-10 flex flex-col justify-between h-full px-12 py-10">
                        {/* Logo */}
                        <Link href="/" className="flex items-center gap-3">
                            <LogoMark className="h-10 w-10" />
                            <LogoWordmark className="text-2xl" dark />
                        </Link>

                        {/* Hero text */}
                        <div>
                            <p className="text-sm font-semibold uppercase tracking-widest mb-4" style={{ color: '#C6900F' }}>
                                Rent anything in Sri Lanka
                            </p>
                            <h1 className="text-4xl font-bold leading-tight text-white mb-6">
                                Everything you need,<br />
                                <span style={{ color: '#AFCBEF' }}>when you need it.</span>
                            </h1>
                            <p className="text-base leading-relaxed" style={{ color: '#7aa5cf' }}>
                                Cameras, vehicles, event gear, tools and more — rented from verified locals with deposit protection built in.
                            </p>

                            {/* Trust pills */}
                            <div className="mt-8 flex flex-wrap gap-3">
                                {['Deposit protected', 'Verified listers', 'QR pickup'].map((t) => (
                                    <span
                                        key={t}
                                        className="rounded-full px-4 py-1.5 text-xs font-semibold"
                                        style={{ backgroundColor: 'rgba(255,255,255,0.08)', color: '#AFCBEF', border: '1px solid rgba(175,203,239,0.2)' }}
                                    >
                                        {t}
                                    </span>
                                ))}
                            </div>
                        </div>

                        {/* Footer quote */}
                        <p className="text-xs" style={{ color: '#4a6f99' }}>
                            © {new Date().getFullYear()} RentCeylon · Colombo, Sri Lanka
                        </p>
                    </div>
                </div>

                {/* ── Right panel — form ── */}
                <div className="flex flex-1 flex-col bg-white">
                    {/* Mobile logo */}
                    <div className="flex items-center justify-between px-6 py-5 lg:hidden">
                        <Link href="/" className="flex items-center gap-2">
                            <LogoMark className="h-8 w-8" />
                            <LogoWordmark className="text-lg" />
                        </Link>
                    </div>

                    <div className="flex flex-1 items-center justify-center px-6 py-8 lg:px-16">
                        <div className="w-full max-w-md">

                            {/* Header */}
                            <div className="mb-8">
                                <h2 className="text-3xl font-bold text-gray-900">Welcome back</h2>
                                <p className="mt-2 text-gray-500">Sign in to your RentCeylon account</p>
                            </div>

                            {status && (
                                <div className="mb-5 rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm font-medium text-emerald-700">
                                    {status}
                                </div>
                            )}

                            {/* Google */}
                            <GoogleButton label="Continue with Google" />

                            <Divider />

                            {/* Email form */}
                            <form onSubmit={submit} className="space-y-4">
                                {/* Email */}
                                <div>
                                    <label className="mb-1.5 block text-sm font-semibold text-gray-700" htmlFor="email">
                                        Email address
                                    </label>
                                    <div className="relative">
                                        <Mail className="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                        <input
                                            id="email"
                                            type="email"
                                            value={data.email}
                                            onChange={(e) => setData('email', e.target.value)}
                                            autoComplete="username"
                                            autoFocus
                                            placeholder="you@example.com"
                                            className="w-full rounded-xl border border-gray-300 py-3 pl-10 pr-4 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                        />
                                    </div>
                                    <InputError message={errors.email} className="mt-1.5" />
                                </div>

                                {/* Password */}
                                <div>
                                    <div className="mb-1.5 flex items-center justify-between">
                                        <label className="text-sm font-semibold text-gray-700" htmlFor="password">Password</label>
                                        {canResetPassword && (
                                            <Link href={route('password.request')} className="text-xs font-medium text-blue-600 hover:underline" style={{ color: '#123063' }}>
                                                Forgot password?
                                            </Link>
                                        )}
                                    </div>
                                    <div className="relative">
                                        <Lock className="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
                                        <input
                                            id="password"
                                            type={showPassword ? 'text' : 'password'}
                                            value={data.password}
                                            onChange={(e) => setData('password', e.target.value)}
                                            autoComplete="current-password"
                                            placeholder="••••••••"
                                            className="w-full rounded-xl border border-gray-300 py-3 pl-10 pr-11 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                                        />
                                        <button
                                            type="button"
                                            onClick={() => setShowPassword((v) => !v)}
                                            className="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
                                            tabIndex={-1}
                                        >
                                            {showPassword ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                                        </button>
                                    </div>
                                    <InputError message={errors.password} className="mt-1.5" />
                                </div>

                                {/* Remember me */}
                                <label className="flex items-center gap-2.5 cursor-pointer select-none">
                                    <Checkbox
                                        name="remember"
                                        checked={data.remember}
                                        onChange={(e) => setData('remember', (e.target.checked || false) as false)}
                                    />
                                    <span className="text-sm text-gray-600">Keep me signed in</span>
                                </label>

                                {/* Submit */}
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="mt-2 w-full rounded-xl py-3.5 text-sm font-bold text-white shadow-md transition active:scale-[0.98] disabled:opacity-60"
                                    style={{ backgroundColor: '#123063' }}
                                >
                                    {processing ? 'Signing in…' : 'Sign in'}
                                </button>
                            </form>

                            {/* Sign up link */}
                            <p className="mt-6 text-center text-sm text-gray-500">
                                Don't have an account?{' '}
                                <Link href="/register" className="font-semibold hover:underline" style={{ color: '#123063' }}>
                                    Create one free
                                </Link>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
