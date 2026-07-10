import { Link, router, usePage } from '@inertiajs/react';
import { PropsWithChildren, useEffect, useState } from 'react';
import { Search, Menu, Globe, X, CheckCircle2, AlertCircle } from 'lucide-react';
import { PageProps } from '@/types/app';
import Icon from '@/Components/site/Icon';
import Logo from '@/Components/site/Logo';
import BottomNav from '@/Components/site/BottomNav';

function UserMenu() {
    const { auth } = usePage<PageProps>().props;
    const [open, setOpen] = useState(false);

    return (
        <div className="relative">
            <button
                onClick={() => setOpen((o) => !o)}
                className="flex items-center gap-2 rounded-full border border-gray-300 py-1.5 pl-3 pr-1.5 shadow-sm transition hover:shadow-md"
            >
                <Menu className="h-4 w-4 text-gray-700" />
                <span className="flex h-8 w-8 items-center justify-center rounded-full bg-gray-900 text-sm font-semibold text-white">
                    {auth.user ? auth.user.name.charAt(0).toUpperCase() : '?'}
                </span>
            </button>

            {open && (
                <>
                    <div className="fixed inset-0 z-10" onClick={() => setOpen(false)} />
                    <div className="absolute right-0 z-20 mt-2 w-60 overflow-hidden rounded-2xl border border-gray-100 bg-white py-2 shadow-hover">
                        {auth.user ? (
                            <>
                                <div className="px-4 py-2 text-sm">
                                    <p className="font-semibold text-gray-900">{auth.user.name}</p>
                                    <p className="capitalize text-gray-500">{auth.user.role}</p>
                                </div>
                                <hr className="my-1" />
                                <MenuLink href="/dashboard">Dashboard</MenuLink>
                                {auth.user.role === 'renter' && <MenuLink href="/rentals">My rentals</MenuLink>}
                                {auth.user.role === 'lister' && <MenuLink href="/lister/listings">My listings</MenuLink>}
                                {auth.user.role === 'lister' && <MenuLink href="/lister/bookings">Incoming bookings</MenuLink>}
                                <MenuLink href="/profile">Profile & settings</MenuLink>
                                <hr className="my-1" />
                                <button
                                    onClick={() => router.post('/logout')}
                                    className="block w-full px-4 py-2 text-left text-sm text-gray-700 hover:bg-gray-50"
                                >
                                    Log out
                                </button>
                            </>
                        ) : (
                            <>
                                <MenuLink href="/register">Sign up</MenuLink>
                                <MenuLink href="/login">Log in</MenuLink>
                                <hr className="my-1" />
                                <MenuLink href="/register?role=lister">List your item</MenuLink>
                                <MenuLink href="/property-management">Property management</MenuLink>
                            </>
                        )}
                    </div>
                </>
            )}
        </div>
    );
}

function MenuLink({ href, children }: PropsWithChildren<{ href: string }>) {
    return (
        <Link href={href} className="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
            {children}
        </Link>
    );
}

function SearchPill({ fullWidth = false }: { fullWidth?: boolean }) {
    const [q, setQ] = useState('');
    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/browse', q ? { q } : {});
    };
    return (
        <form onSubmit={submit} className={`flex items-center rounded-full border border-gray-300 py-1.5 pl-5 pr-1.5 shadow-sm transition hover:shadow-md ${fullWidth ? 'w-full' : ''}`}>
            <input
                value={q}
                onChange={(e) => setQ(e.target.value)}
                placeholder="Search anything to rent…"
                className={`border-0 p-0 text-sm focus:ring-0 ${fullWidth ? 'flex-1' : 'w-40 sm:w-56'}`}
            />
            <button type="submit" className="flex-shrink-0 rounded-full bg-gold-500 p-2 text-white hover:bg-gold-600" aria-label="Search">
                <Search className="h-4 w-4" />
            </button>
        </form>
    );
}

function CategoryBar() {
    const { nav } = usePage<PageProps>().props;
    return (
        <div className="border-t border-gray-100">
            <div className="mx-auto flex max-w-7xl gap-6 overflow-x-auto px-4 py-3 sm:px-6 lg:px-8">
                {nav.categories.map((c) => (
                    <Link
                        key={c.id}
                        href={`/browse?category=${c.slug}`}
                        className="flex flex-shrink-0 flex-col items-center gap-1.5 border-b-2 border-transparent pb-1 text-gray-500 transition hover:border-gray-300 hover:text-gray-900"
                    >
                        <Icon name={c.icon} className="h-6 w-6" />
                        <span className="whitespace-nowrap text-xs font-medium">{c.name}</span>
                    </Link>
                ))}
            </div>
        </div>
    );
}

function Flash() {
    const { flash } = usePage<PageProps>().props;
    const [show, setShow] = useState(true);
    useEffect(() => { setShow(true); }, [flash.success, flash.error]);
    if (!show || (!flash.success && !flash.error)) return null;
    const ok = !!flash.success;
    return (
        <div className={`fixed bottom-20 left-1/2 z-50 flex -translate-x-1/2 items-center gap-2 rounded-xl px-4 py-3 text-sm text-white shadow-hover sm:bottom-5 ${ok ? 'bg-emerald-600' : 'bg-rose-600'}`}>
            {ok ? <CheckCircle2 className="h-5 w-5" /> : <AlertCircle className="h-5 w-5" />}
            <span>{flash.success ?? flash.error}</span>
            <button onClick={() => setShow(false)}><X className="h-4 w-4" /></button>
        </div>
    );
}

function Footer() {
    return (
        <footer className="mt-16 border-t border-gray-200 bg-gray-50">
            <div className="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
                <div>
                    <Logo />
                    <p className="mt-3 text-sm text-gray-500">Rent anything and everything in Sri Lanka. Deposit-protected, verified listers.</p>
                </div>
                <FooterCol title="Discover" links={[['Browse all', '/browse'], ['Vehicles', '/browse?category=vehicles'], ['Electronics', '/browse?category=electronics'], ['Event gear', '/browse?category=events']]} />
                <FooterCol title="RentCeylon" links={[['How it works', '/trust'], ['Pricing for listers', '/pricing'], ['Property management', '/property-management'], ['Trust & safety', '/trust']]} />
                <FooterCol title="Account" links={[['Log in', '/login'], ['Sign up', '/register'], ['List your item', '/register?role=lister']]} />
            </div>
            <div className="border-t border-gray-200 py-4 text-center text-xs text-gray-500">
                © {new Date().getFullYear()} RentCeylon · PayHere · iPay · Colombo, Sri Lanka
            </div>
        </footer>
    );
}

function FooterCol({ title, links }: { title: string; links: [string, string][] }) {
    return (
        <div>
            <h4 className="mb-3 text-sm font-semibold text-gray-900">{title}</h4>
            <ul className="space-y-2 text-sm text-gray-500">
                {links.map(([label, href]) => (
                    <li key={href}><Link href={href} className="hover:text-gray-900 hover:underline">{label}</Link></li>
                ))}
            </ul>
        </div>
    );
}

export default function SiteLayout({ children, showCategories = false }: PropsWithChildren<{ showCategories?: boolean }>) {
    const { auth } = usePage<PageProps>().props;
    return (
        <div className="flex min-h-screen flex-col">
            <header className="sticky top-0 z-30 bg-white/95 shadow-nav backdrop-blur">
                <div className="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">
                    <Logo markClassName="h-8 w-8 sm:h-9 sm:w-9" wordmarkClassName="text-lg sm:text-xl" />
                    <div className="hidden md:block"><SearchPill /></div>
                    <div className="flex items-center gap-2">
                        {!auth.user && (
                            <Link href="/register?role=lister" className="hidden rounded-full px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 lg:block">
                                List your item
                            </Link>
                        )}
                        <button className="hidden rounded-full p-2.5 text-gray-700 hover:bg-gray-100 sm:block" aria-label="Language"><Globe className="h-5 w-5" /></button>
                        <UserMenu />
                    </div>
                </div>

                {/* Mobile: full-width "Start your search" row, own line under the logo */}
                <div className="px-4 pb-3 md:hidden">
                    <SearchPill fullWidth />
                </div>

                {showCategories && <CategoryBar />}
            </header>

            {/* pb reserves room for the fixed mobile bottom tab bar */}
            <main className="flex-1 pb-16 sm:pb-0">{children}</main>
            <Footer />
            <Flash />
            <BottomNav />
        </div>
    );
}
