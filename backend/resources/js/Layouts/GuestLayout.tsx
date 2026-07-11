import Logo from '@/Components/site/Logo';
import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

function GuestNav() {
    return (
        <header className="border-b border-gray-100 bg-white">
            <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">
                <Logo markClassName="h-8 w-8" wordmarkClassName="text-lg" />
                <nav className="flex items-center gap-1">
                    <Link
                        href="/login"
                        className="rounded-full px-4 py-2 text-sm font-medium text-gray-700 transition hover:bg-gray-100"
                    >
                        Log in
                    </Link>
                    <Link
                        href="/register"
                        className="rounded-full px-4 py-2 text-sm font-semibold text-white transition"
                        style={{ backgroundColor: '#123063' }}
                    >
                        Sign up
                    </Link>
                </nav>
            </div>
        </header>
    );
}

function GuestFooter() {
    return (
        <footer style={{ backgroundColor: '#123063' }}>
            <div className="mx-auto flex max-w-7xl flex-col items-center gap-4 px-4 py-10 sm:flex-row sm:justify-between sm:px-6 lg:px-8">
                <Logo
                    markClassName="h-8 w-8"
                    wordmarkClassName="text-lg"
                    asLink={false}
                    dark
                />
                <nav className="flex flex-wrap justify-center gap-x-6 gap-y-2 text-sm text-blue-200">
                    <Link href="/browse" className="hover:text-white transition">Browse</Link>
                    <Link href="/trust" className="hover:text-white transition">Trust & Safety</Link>
                    <Link href="/pricing" className="hover:text-white transition">Pricing</Link>
                    <Link href="/register?role=lister" className="hover:text-white transition">List your item</Link>
                </nav>
                <p className="text-xs text-blue-300">
                    © {new Date().getFullYear()} RentCeylon · Colombo, Sri Lanka
                </p>
            </div>
        </footer>
    );
}

export default function GuestLayout({ children }: PropsWithChildren) {
    return (
        <div className="flex min-h-screen flex-col bg-gray-50">
            <GuestNav />

            <main className="flex flex-1 flex-col items-center justify-center px-4 py-10">
                <div className="w-full max-w-md overflow-hidden rounded-2xl bg-white px-6 py-8 shadow-lg">
                    {children}
                </div>
            </main>

            <GuestFooter />
        </div>
    );
}
