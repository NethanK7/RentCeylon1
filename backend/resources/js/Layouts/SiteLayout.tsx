import { Link, router, usePage } from '@inertiajs/react';
import { PropsWithChildren, useEffect, useRef, useState } from 'react';
import { Search, Menu, X, CheckCircle2, AlertCircle, ArrowRight, Clock, MapPin, Tag } from 'lucide-react';
import { PageProps } from '@/types/app';
import Icon from '@/Components/site/Icon';
import Logo from '@/Components/site/Logo';
import BottomNav from '@/Components/site/BottomNav';

// ─── Dynamic Island Search ────────────────────────────────────────────────────

const POPULAR_SEARCHES = ['Camera', 'Car', 'Drone', 'DJ Equipment', 'Tent', 'Scooter', 'Projector', 'Kayak'];
const TOP_CITIES = ['Colombo', 'Kandy', 'Galle', 'Negombo', 'Matara'];

function DynamicSearch({ fullWidth = false }: { fullWidth?: boolean }) {
    const { nav } = usePage<PageProps>().props;
    const [open, setOpen] = useState(false);
    const [q, setQ] = useState('');
    const inputRef = useRef<HTMLInputElement>(null);
    const wrapRef = useRef<HTMLDivElement>(null);

    const filtered = POPULAR_SEARCHES.filter((s) => !q || s.toLowerCase().includes(q.toLowerCase()));
    const cities = TOP_CITIES.filter((c) => !q || c.toLowerCase().includes(q.toLowerCase()));
    const cats = nav.categories.filter((c) => !q || c.name.toLowerCase().includes(q.toLowerCase())).slice(0, 6);

    const submit = (value?: string) => {
        const term = value ?? q;
        setOpen(false);
        setQ(term);
        router.get('/browse', term ? { q: term } : {});
    };

    const submitCity = (city: string) => {
        setOpen(false);
        setQ('');
        router.get('/browse', { city });
    };

    const submitCat = (slug: string) => {
        setOpen(false);
        setQ('');
        router.get('/browse', { category: slug });
    };

    // Close on outside click
    useEffect(() => {
        const handler = (e: MouseEvent) => {
            if (wrapRef.current && !wrapRef.current.contains(e.target as Node)) setOpen(false);
        };
        document.addEventListener('mousedown', handler);
        return () => document.removeEventListener('mousedown', handler);
    }, []);

    // Focus input when opening
    useEffect(() => { if (open) setTimeout(() => inputRef.current?.focus(), 60); }, [open]);

    const showSuggestions = filtered.length || cities.length || cats.length;

    return (
        <>
            {/* Backdrop */}
            <div
                onClick={() => setOpen(false)}
                className="fixed inset-0 z-30 bg-black/20 backdrop-blur-[2px] transition-opacity duration-300"
                style={{ opacity: open ? 1 : 0, pointerEvents: open ? 'auto' : 'none' }}
            />

            {/* The pill / expanded bar */}
            <div
                ref={wrapRef}
                className="relative z-40"
                style={{ width: fullWidth ? '100%' : undefined }}
            >
                {/* Collapsed pill — shown when closed */}
                {!open && (
                    <button
                        onClick={() => setOpen(true)}
                        className={`flex items-center gap-2 rounded-full border border-gray-300 bg-white py-2 pl-4 pr-2 shadow-sm transition-all duration-300 hover:shadow-md ${fullWidth ? 'w-full' : ''}`}
                    >
                        <Search className="h-4 w-4 flex-shrink-0 text-gray-500" />
                        <span className={`text-sm text-gray-400 ${fullWidth ? 'flex-1 text-left' : 'w-36 sm:w-52'}`}>
                            {q || 'Search anything to rent…'}
                        </span>
                        <span className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-full" style={{ backgroundColor: '#C6900F' }}>
                            <Search className="h-3.5 w-3.5 text-white" />
                        </span>
                    </button>
                )}

                {/* Expanded panel */}
                <div
                    className="absolute left-1/2 top-0 overflow-hidden rounded-3xl bg-white shadow-2xl"
                    style={{
                        transform: open ? 'translateX(-50%) scaleX(1) scaleY(1)' : 'translateX(-50%) scaleX(0.6) scaleY(0.5)',
                        opacity: open ? 1 : 0,
                        pointerEvents: open ? 'auto' : 'none',
                        width: fullWidth ? '100vw' : 'min(580px, 90vw)',
                        transformOrigin: 'top center',
                        transition: 'transform 0.28s cubic-bezier(0.34,1.56,0.64,1), opacity 0.2s ease',
                        maxHeight: '80vh',
                    }}
                >
                    {/* Input row */}
                    <div className="flex items-center gap-3 border-b border-gray-100 px-4 py-3">
                        <Search className="h-4 w-4 flex-shrink-0 text-gray-400" />
                        <input
                            ref={inputRef}
                            value={q}
                            onChange={(e) => setQ(e.target.value)}
                            onKeyDown={(e) => e.key === 'Enter' && submit()}
                            placeholder="Search anything to rent…"
                            className="flex-1 border-0 bg-transparent p-0 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0"
                        />
                        {q && (
                            <button onClick={() => setQ('')} className="rounded-full p-1 text-gray-400 hover:bg-gray-100">
                                <X className="h-4 w-4" />
                            </button>
                        )}
                        <button
                            onClick={() => submit()}
                            className="flex items-center gap-1.5 rounded-full px-4 py-2 text-sm font-semibold text-white"
                            style={{ backgroundColor: '#123063' }}
                        >
                            Search <ArrowRight className="h-3.5 w-3.5" />
                        </button>
                    </div>

                    {/* Suggestions body */}
                    <div className="overflow-y-auto" style={{ maxHeight: '60vh' }}>
                        {/* Typed search result shortcut */}
                        {q && (
                            <button
                                onClick={() => submit(q)}
                                className="flex w-full items-center gap-3 px-5 py-3 text-left text-sm font-semibold text-gray-900 hover:bg-gray-50"
                            >
                                <Search className="h-4 w-4 text-gray-400" />
                                Search for "<span style={{ color: '#123063' }}>{q}</span>"
                            </button>
                        )}

                        {/* Popular searches */}
                        {filtered.length > 0 && (
                            <div className="px-5 pt-4">
                                <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">
                                    {q ? 'Suggestions' : 'Popular searches'}
                                </p>
                                <div className="flex flex-wrap gap-2 pb-2">
                                    {filtered.slice(0, 8).map((s) => (
                                        <button
                                            key={s}
                                            onClick={() => submit(s)}
                                            className="flex items-center gap-1.5 rounded-full border border-gray-200 bg-gray-50 px-3 py-1.5 text-xs font-medium text-gray-700 transition hover:border-gray-400 hover:bg-white"
                                        >
                                            <Clock className="h-3 w-3 text-gray-400" />
                                            {s}
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Cities */}
                        {cities.length > 0 && (
                            <div className="border-t border-gray-100 px-5 pt-4">
                                <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Browse by city</p>
                                <div className="space-y-0.5 pb-3">
                                    {cities.slice(0, 5).map((city) => (
                                        <button
                                            key={city}
                                            onClick={() => submitCity(city)}
                                            className="flex w-full items-center gap-3 rounded-xl px-2 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-50"
                                        >
                                            <span className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gray-100">
                                                <MapPin className="h-4 w-4 text-gray-500" />
                                            </span>
                                            <span className="font-medium">{city}</span>
                                            <span className="ml-auto text-xs text-gray-400">Rentals near you</span>
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}

                        {/* Categories */}
                        {cats.length > 0 && (
                            <div className="border-t border-gray-100 px-5 pt-4 pb-4">
                                <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-gray-400">Categories</p>
                                <div className="grid grid-cols-2 gap-1.5">
                                    {cats.map((cat) => (
                                        <button
                                            key={cat.slug}
                                            onClick={() => submitCat(cat.slug)}
                                            className="flex items-center gap-2.5 rounded-xl px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-gray-50"
                                        >
                                            <span className="flex h-7 w-7 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100">
                                                <Tag className="h-3.5 w-3.5 text-gray-500" />
                                            </span>
                                            <span className="font-medium truncate">{cat.name}</span>
                                        </button>
                                    ))}
                                </div>
                            </div>
                        )}

                        {!showSuggestions && q && (
                            <div className="px-5 py-8 text-center text-sm text-gray-400">
                                No suggestions — hit Search to see results
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}

// ─── User menu ────────────────────────────────────────────────────────────────

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

// ─── Category bar ─────────────────────────────────────────────────────────────

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

// ─── Flash ────────────────────────────────────────────────────────────────────

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

// ─── Footer ───────────────────────────────────────────────────────────────────

function Footer() {
    return (
        <footer className="mt-16" style={{ backgroundColor: '#123063' }}>
            <div className="mx-auto grid max-w-7xl gap-8 px-4 py-12 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
                <div>
                    <Logo asLink={false} dark />
                    <p className="mt-3 text-sm" style={{ color: '#93b8e0' }}>Rent anything and everything in Sri Lanka. Deposit-protected, verified listers.</p>
                </div>
                <FooterCol title="Discover" links={[['Browse all', '/browse'], ['Vehicles', '/browse?category=vehicles'], ['Electronics', '/browse?category=electronics'], ['Event gear', '/browse?category=events']]} />
                <FooterCol title="RentCeylon" links={[['How it works', '/trust'], ['Pricing for listers', '/pricing'], ['Property management', '/property-management'], ['Trust & safety', '/trust']]} />
                <FooterCol title="Account" links={[['Log in', '/login'], ['Sign up', '/register'], ['List your item', '/register?role=lister']]} />
            </div>
            <div className="py-4 text-center text-xs" style={{ borderTop: '1px solid rgba(255,255,255,0.1)', color: '#6b9ec8' }}>
                © {new Date().getFullYear()} RentCeylon · PayHere · iPay · Colombo, Sri Lanka
            </div>
        </footer>
    );
}

function FooterCol({ title, links }: { title: string; links: [string, string][] }) {
    return (
        <div>
            <h4 className="mb-3 text-sm font-semibold text-white">{title}</h4>
            <ul className="space-y-2 text-sm" style={{ color: '#93b8e0' }}>
                {links.map(([label, href]) => (
                    <li key={href}><Link href={href} className="transition hover:text-white">{label}</Link></li>
                ))}
            </ul>
        </div>
    );
}

// ─── Layout ───────────────────────────────────────────────────────────────────

export default function SiteLayout({ children, showCategories = false }: PropsWithChildren<{ showCategories?: boolean }>) {
    const { auth } = usePage<PageProps>().props;
    return (
        <div className="flex min-h-screen flex-col">
            <header className="sticky top-0 z-30 bg-white/95 shadow-nav backdrop-blur">
                <div className="mx-auto flex max-w-screen-2xl items-center justify-between gap-3 px-4 py-3 sm:px-6 lg:px-8">
                    {/* Logo */}
                    <Logo markClassName="h-8 w-8 sm:h-9 sm:w-9" wordmarkClassName="text-lg sm:text-xl" />

                    {/* Desktop search — centre */}
                    <div className="hidden flex-1 items-center justify-center gap-4 md:flex">
                        <DynamicSearch />
                        <Link
                            href="/property-management"
                            className="hidden whitespace-nowrap rounded-full border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 transition hover:border-gray-400 hover:bg-gray-50 lg:block"
                        >
                            Property management
                        </Link>
                    </div>

                    {/* Right actions */}
                    <div className="flex items-center gap-2">
                        {!auth.user && (
                            <Link href="/register?role=lister" className="hidden rounded-full px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-100 lg:block">
                                List your item
                            </Link>
                        )}
                        <UserMenu />
                    </div>
                </div>

                {/* Mobile search row */}
                <div className="px-4 pb-3 md:hidden">
                    <DynamicSearch fullWidth />
                </div>

                {showCategories && <CategoryBar />}
            </header>

            <main className="flex-1 pb-16 sm:pb-0">{children}</main>
            <Footer />
            <Flash />
            <BottomNav />
        </div>
    );
}
