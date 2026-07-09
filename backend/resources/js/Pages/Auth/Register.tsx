import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { KeyRound, PackageSearch } from 'lucide-react';

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
        accept_tos: false as boolean, // must be explicitly ticked (Constraint 10)
        referral_code: referralCode ?? '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('register'), { onFinish: () => reset('password', 'password_confirmation') });
    };

    return (
        <GuestLayout>
            <Head title="Sign up" />

            <h1 className="mb-1 text-xl font-bold text-gray-900">Create your RentCeylon account</h1>
            <p className="mb-5 text-sm text-gray-500">Rent, or list your own items — you choose.</p>

            <form onSubmit={submit}>
                {/* Role selection */}
                <div className="mb-4 grid grid-cols-2 gap-3">
                    <RoleCard active={data.role === 'renter'} onClick={() => setData('role', 'renter')} icon={<PackageSearch className="h-5 w-5" />} title="I want to rent" desc="Browse & book items" />
                    <RoleCard active={data.role === 'lister'} onClick={() => setData('role', 'lister')} icon={<KeyRound className="h-5 w-5" />} title="I want to list" desc="Earn from your items" />
                </div>
                <InputError message={errors.role} className="mt-1" />

                <div className="mt-4">
                    <InputLabel htmlFor="name" value="Full name" />
                    <TextInput id="name" name="name" value={data.name} className="mt-1 block w-full" autoComplete="name" isFocused onChange={(e) => setData('name', e.target.value)} required />
                    <InputError message={errors.name} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="email" value="Email" />
                    <TextInput id="email" type="email" name="email" value={data.email} className="mt-1 block w-full" autoComplete="username" onChange={(e) => setData('email', e.target.value)} required />
                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="phone" value="Phone (Sri Lanka)" />
                    <TextInput id="phone" name="phone" value={data.phone} className="mt-1 block w-full" placeholder="07X XXX XXXX" onChange={(e) => setData('phone', e.target.value)} required />
                    <InputError message={errors.phone} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value="Password" />
                    <TextInput id="password" type="password" name="password" value={data.password} className="mt-1 block w-full" autoComplete="new-password" onChange={(e) => setData('password', e.target.value)} required />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password_confirmation" value="Confirm password" />
                    <TextInput id="password_confirmation" type="password" name="password_confirmation" value={data.password_confirmation} className="mt-1 block w-full" autoComplete="new-password" onChange={(e) => setData('password_confirmation', e.target.value)} required />
                    <InputError message={errors.password_confirmation} className="mt-2" />
                </div>

                {/* ToS — cannot be pre-checked; off-platform clause prominent (Constraint 10) */}
                <div className="mt-5 rounded-xl bg-gray-50 p-3">
                    <label className="flex items-start gap-2 text-sm text-gray-700">
                        <input type="checkbox" checked={data.accept_tos} onChange={(e) => setData('accept_tos', e.target.checked)} className="mt-0.5 rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                        <span>
                            I agree to the <a href="/trust" className="font-semibold underline">Terms of Service</a>.
                            I understand that <strong>dealing off-platform (sharing phone/payment details to avoid fees) can result in account suspension.</strong>
                        </span>
                    </label>
                    <InputError message={errors.accept_tos} className="mt-2" />
                </div>

                <button type="submit" disabled={processing} className="btn-primary mt-5 w-full">
                    {data.role === 'lister' ? 'Create account & verify ID' : 'Create account'}
                </button>

                <p className="mt-4 text-center text-sm text-gray-600">
                    Already registered? <Link href={route('login')} className="font-semibold text-brand-600 underline">Log in</Link>
                </p>
            </form>
        </GuestLayout>
    );
}

function RoleCard({ active, onClick, icon, title, desc }: { active: boolean; onClick: () => void; icon: React.ReactNode; title: string; desc: string }) {
    return (
        <button type="button" onClick={onClick}
            className={`rounded-xl border p-3 text-left transition ${active ? 'border-brand-500 bg-brand-50 ring-1 ring-brand-500' : 'border-gray-300 hover:border-gray-400'}`}>
            <span className={active ? 'text-brand-600' : 'text-gray-500'}>{icon}</span>
            <p className="mt-1.5 text-sm font-semibold text-gray-900">{title}</p>
            <p className="text-xs text-gray-500">{desc}</p>
        </button>
    );
}
