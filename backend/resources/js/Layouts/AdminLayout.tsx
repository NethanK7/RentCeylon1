import { Link, router, usePage } from '@inertiajs/react';
import { PropsWithChildren } from 'react';
import { LayoutDashboard, Users, ShieldCheck, ListChecks, CalendarDays, Star, LogOut, Menu } from 'lucide-react';
import { PageProps } from '@/types/app';
import { Head } from '@inertiajs/react';
import Logo from '@/Components/site/Logo';

const nav = [
    { href: '/admin',              label: 'Dashboard',      Icon: LayoutDashboard },
    { href: '/admin/users',        label: 'Users',          Icon: Users },
    { href: '/admin/verifications',label: 'ID Verifications', Icon: ShieldCheck },
    { href: '/admin/listings',     label: 'Listings',       Icon: ListChecks },
    { href: '/admin/bookings',     label: 'Bookings',       Icon: CalendarDays },
    { href: '/admin/reviews',      label: 'Reviews',        Icon: Star },
];

export default function AdminLayout({ title, children }: PropsWithChildren<{ title: string }>) {
    const { flash } = usePage<PageProps>().props;

    return (
        <div className="min-h-screen bg-gray-50">
            <Head title={`Admin · ${title}`} />

            {/* Sidebar */}
            <aside className="fixed inset-y-0 left-0 z-30 w-56 bg-gray-900 flex flex-col">
                <div className="flex h-14 items-center px-4 border-b border-gray-700">
                    <Link href="/admin" className="text-white font-bold text-lg tracking-tight">
                        RC <span className="text-rose-400">Admin</span>
                    </Link>
                </div>
                <nav className="flex-1 py-4 space-y-0.5 px-2">
                    {nav.map(({ href, label, Icon }) => {
                        const active = typeof window !== 'undefined'
                            ? window.location.pathname === href || (href !== '/admin' && window.location.pathname.startsWith(href))
                            : false;
                        return (
                            <Link
                                key={href}
                                href={href}
                                className={`flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition ${
                                    active ? 'bg-gray-700 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white'
                                }`}
                            >
                                <Icon className="h-4 w-4 flex-shrink-0" />
                                {label}
                            </Link>
                        );
                    })}
                </nav>
                <div className="p-2 border-t border-gray-700">
                    <button
                        onClick={() => router.post('/logout')}
                        className="flex w-full items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-400 hover:bg-gray-800 hover:text-white transition"
                    >
                        <LogOut className="h-4 w-4" />
                        Log out
                    </button>
                </div>
            </aside>

            {/* Main */}
            <div className="pl-56">
                <header className="sticky top-0 z-20 bg-white border-b border-gray-200 h-14 flex items-center px-6">
                    <h1 className="font-semibold text-gray-900">{title}</h1>
                </header>

                <main className="p-6">
                    {flash?.success && (
                        <div className="mb-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800">
                            {flash.success}
                        </div>
                    )}
                    {flash?.error && (
                        <div className="mb-4 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800">
                            {flash.error}
                        </div>
                    )}
                    {children}
                </main>
            </div>
        </div>
    );
}
