import InputError from '@/Components/InputError';
import { LogoMark, LogoWordmark } from '@/Components/site/Logo';
import { Head, Link, useForm } from '@inertiajs/react';
import { Eye, EyeOff, KeyRound, Lock, Mail, PackageSearch, Phone, User } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

function GoogleButton({ label = 'Sign up with Google' }: { label?: string }) {
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

function FieldInput({ id, type = 'text', icon: Icon, placeholder, value, onChange, autoFocus }: {
    id: string; type?: string; icon: React.ElementType; placeholder: string;
    value: string; onChange: (v: string) => void; autoFocus?: boolean;
}) {
    const [show, setShow] = useState(false);
    const isPassword = type === 'password';
    return (
        <div className="relative">
            <Icon className="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-gray-400" />
            <input
                id={id}
                type={isPassword ? (show ? 'text' : 'password') : type}
                value={value}
                onChange={(e) => onChange(e.target.value)}
                placeholder={placeholder}
                autoFocus={autoFocus}
                className="w-full rounded-xl border border-gray-300 py-3 pl-10 pr-4 text-sm text-gray-900 placeholder-gray-400 shadow-sm transition focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-100"
                style={isPassword ? { paddingRight: '2.75rem' } : {}}
            />
            {isPassword && (
                <button type="button" tabIndex={-1} onClick={() => setShow(v => !v)}
                    className="absolute right-3.5 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                    {show ? <EyeOff className="h-4 w-4" /> : <Eye className="h-4 w-4" />}
                </button>
            )}
        </div>
    );
}

function RoleCard({ active, onClick, icon, title, desc }: {
    active: boolean; onClick: () => void; icon: React.ReactNode; title: string; desc: string;
}) {
    return (
        <button type="button" onClick={onClick}
            className="flex flex-col items-start rounded-xl border-2 p-4 text-left transition active:scale-[0.98]"
            style={active
                ? { borderColor: '#123063', backgroundColor: 'rgba(18,48,99,0.04)' }
                : { borderColor: '#e5e7eb' }
            }
        >
            <span className={`mb-2 rounded-lg p-2`} style={{ backgroundColor: active ? 'rgba(18,48,99,0.1)' : '#f3f4f6' }}>
                <span style={{ color: active ? '#123063' : '#6b7280' }}>{icon}</span>
            </span>
            <p className="text-sm font-bold text-gray-900">{title}</p>
            <p className="text-xs text-gray-500 mt-0.5">{desc}</p>
        </button>
    );
}

export default function Register({ referralCode }: { referralCode?: string }) {
    const params = new URLSearchParams(window.location.search);
    const initialRole = params.get('role') === 'lister' ? 'lister' : 'renter';

    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
        role: initialRole,
        accept_tos: false as boolean,
        referral_code: referralCode ?? '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), { onFinish: () => reset('password', 'password_confirmation') });
    };

    return (
        <>
            <Head title="Create account · RentCeylon" />

            <div className="flex min-h-screen">
                {/* ── Left panel ── */}
                <div
                    className="relative hidden w-[45%] flex-col overflow-hidden lg:flex"
                    style={{ backgroundColor: '#0c1e40' }}
                >
                    <div className="absolute inset-0" style={{
                        background: 'linear-gradient(135deg, #0c1e40 0%, #1a3a6e 50%, #0f2850 100%)',
                    }} />
                    <div className="absolute -top-24 -right-24 h-96 w-96 rounded-full opacity-10" style={{ backgroundColor: '#3868AE' }} />
                    <div className="absolute bottom-10 -left-16 h-72 w-72 rounded-full opacity-10" style={{ backgroundColor: '#C6900F' }} />

                    <div className="relative z-10 flex flex-col justify-between h-full px-12 py-10">
                        <Link href="/" className="flex items-center gap-3">
                            <LogoMark className="h-10 w-10" />
                            <LogoWordmark className="text-2xl" dark />
                        </Link>

                        <div>
                            <p className="text-sm font-semibold uppercase tracking-widest mb-4" style={{ color: '#C6900F' }}>
                                Join RentCeylon today
                            </p>
                            <h1 className="text-4xl font-bold leading-tight text-white mb-6">
                                Rent from locals.<br />
                                <span style={{ color: '#AFCBEF' }}>Earn from your items.</span>
                            </h1>

                            {/* Feature list */}
                            <div className="space-y-4 mt-8">
                                {[
                                    { icon: '🔒', title: 'Deposit escrow', desc: 'Security deposits held safely until return confirmed' },
                                    { icon: '✅', title: 'Verified listers', desc: 'Every lister ID-verified before publishing' },
                                    { icon: '📱', title: 'QR pickup', desc: 'Frictionless handover — scan and go' },
                                ].map((f) => (
                                    <div key={f.title} className="flex items-start gap-3">
                                        <span className="text-xl">{f.icon}</span>
                                        <div>
                                            <p className="text-sm font-semibold text-white">{f.title}</p>
                                            <p className="text-xs mt-0.5" style={{ color: '#7aa5cf' }}>{f.desc}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>

                        <p className="text-xs" style={{ color: '#4a6f99' }}>
                            © {new Date().getFullYear()} RentCeylon · Colombo, Sri Lanka
                        </p>
                    </div>
                </div>

                {/* ── Right panel — form ── */}
                <div className="flex flex-1 flex-col bg-white overflow-y-auto">
                    {/* Mobile logo */}
                    <div className="flex items-center justify-between px-6 py-5 lg:hidden">
                        <Link href="/" className="flex items-center gap-2">
                            <LogoMark className="h-8 w-8" />
                            <LogoWordmark className="text-lg" />
                        </Link>
                    </div>

                    <div className="flex flex-1 items-start justify-center px-6 py-8 lg:items-center lg:px-16">
                        <div className="w-full max-w-md">

                            {/* Header */}
                            <div className="mb-7">
                                <h2 className="text-3xl font-bold text-gray-900">Create your account</h2>
                                <p className="mt-2 text-gray-500">Free to join. No subscription required.</p>
                            </div>

                            {/* Google — renter only since listers need ID */}
                            {data.role === 'renter' && (
                                <>
                                    <GoogleButton label="Sign up with Google" />
                                    <Divider />
                                </>
                            )}

                            <form onSubmit={submit} className="space-y-4">

                                {/* Role */}
                                <div>
                                    <p className="mb-2 text-sm font-semibold text-gray-700">I want to…</p>
                                    <div className="grid grid-cols-2 gap-3">
                                        <RoleCard
                                            active={data.role === 'renter'}
                                            onClick={() => setData('role', 'renter')}
                                            icon={<PackageSearch className="h-5 w-5" />}
                                            title="Rent items"
                                            desc="Browse & book from locals"
                                        />
                                        <RoleCard
                                            active={data.role === 'lister'}
                                            onClick={() => setData('role', 'lister')}
                                            icon={<KeyRound className="h-5 w-5" />}
                                            title="List items"
                                            desc="Earn from what you own"
                                        />
                                    </div>
                                    <InputError message={errors.role} className="mt-1" />
                                </div>

                                {/* Full name */}
                                <div>
                                    <label className="mb-1.5 block text-sm font-semibold text-gray-700" htmlFor="name">Full name</label>
                                    <FieldInput id="name" icon={User} placeholder="Your full name" value={data.name} onChange={(v) => setData('name', v)} autoFocus />
                                    <InputError message={errors.name} className="mt-1.5" />
                                </div>

                                {/* Email */}
                                <div>
                                    <label className="mb-1.5 block text-sm font-semibold text-gray-700" htmlFor="email">Email address</label>
                                    <FieldInput id="email" type="email" icon={Mail} placeholder="you@example.com" value={data.email} onChange={(v) => setData('email', v)} />
                                    <InputError message={errors.email} className="mt-1.5" />
                                </div>

                                {/* Phone */}
                                <div>
                                    <label className="mb-1.5 block text-sm font-semibold text-gray-700" htmlFor="phone">Phone (Sri Lanka)</label>
                                    <FieldInput id="phone" icon={Phone} placeholder="07X XXX XXXX" value={data.phone} onChange={(v) => setData('phone', v)} />
                                    <InputError message={errors.phone} className="mt-1.5" />
                                </div>

                                {/* Password */}
                                <div>
                                    <label className="mb-1.5 block text-sm font-semibold text-gray-700" htmlFor="password">Password</label>
                                    <FieldInput id="password" type="password" icon={Lock} placeholder="Min. 8 characters" value={data.password} onChange={(v) => setData('password', v)} />
                                    <InputError message={errors.password} className="mt-1.5" />
                                </div>

                                {/* Confirm password */}
                                <div>
                                    <label className="mb-1.5 block text-sm font-semibold text-gray-700" htmlFor="password_confirmation">Confirm password</label>
                                    <FieldInput id="password_confirmation" type="password" icon={Lock} placeholder="Repeat password" value={data.password_confirmation} onChange={(v) => setData('password_confirmation', v)} />
                                    <InputError message={errors.password_confirmation} className="mt-1.5" />
                                </div>

                                {/* ToS */}
                                <div className="rounded-xl border border-gray-200 bg-gray-50 p-4">
                                    <label className="flex items-start gap-3 cursor-pointer select-none">
                                        <input
                                            type="checkbox"
                                            checked={data.accept_tos}
                                            onChange={(e) => setData('accept_tos', e.target.checked)}
                                            className="mt-0.5 rounded border-gray-300 focus:ring-offset-0"
                                            style={{ accentColor: '#123063' }}
                                        />
                                        <span className="text-xs leading-relaxed text-gray-600">
                                            I agree to the{' '}
                                            <a href="/trust" target="_blank" className="font-semibold underline text-gray-900">Terms of Service</a>.
                                            {' '}Dealing off-platform to avoid fees may result in account suspension.
                                        </span>
                                    </label>
                                    <InputError message={errors.accept_tos} className="mt-2" />
                                </div>

                                {/* Submit */}
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="w-full rounded-xl py-3.5 text-sm font-bold text-white shadow-md transition active:scale-[0.98] disabled:opacity-60"
                                    style={{ backgroundColor: '#123063' }}
                                >
                                    {processing
                                        ? 'Creating account…'
                                        : data.role === 'lister'
                                            ? 'Create account & verify ID →'
                                            : 'Create account'}
                                </button>
                            </form>

                            {/* Sign in link */}
                            <p className="mt-6 text-center text-sm text-gray-500">
                                Already have an account?{' '}
                                <Link href="/login" className="font-semibold hover:underline" style={{ color: '#123063' }}>
                                    Sign in
                                </Link>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
