import { Link, usePage } from '@inertiajs/react';
import { Compass, Heart, Briefcase, User } from 'lucide-react';
import { PageProps } from '@/types/app';

/** Airbnb's signature mobile bottom tab bar. Hidden on desktop (sm:hidden). */
export default function BottomNav() {
    // usePage().url is reactive across Inertia (SPA) navigations — window.location isn't.
    const { props: { auth }, url } = usePage<PageProps>();
    const path = url.split('?')[0];

    const tripsHref = !auth.user
        ? '/login'
        : auth.user.role === 'lister' ? '/lister/bookings'
        : auth.user.role === 'admin' ? '/admin'
        : auth.user.role === 'manager' ? '/manager'
        : '/rentals';

    const tabs = [
        { href: '/browse', label: 'Explore', icon: Compass, match: (p: string) => p === '/' || p.startsWith('/browse') || p.startsWith('/listings') },
        { href: '/wishlist', label: 'Wishlists', icon: Heart, match: (p: string) => p.startsWith('/wishlist') },
        { href: tripsHref, label: auth.user?.role === 'lister' ? 'Bookings' : 'Trips', icon: Briefcase, match: (p: string) => p.startsWith('/rentals') || p.startsWith('/lister/bookings') || p.startsWith('/bookings') },
        { href: auth.user ? '/profile' : '/login', label: auth.user ? 'Profile' : 'Log in', icon: User, match: (p: string) => p.startsWith('/profile') || p.startsWith('/login') },
    ];

    return (
        <nav className="fixed inset-x-0 bottom-0 z-40 border-t border-gray-200 bg-white pb-[env(safe-area-inset-bottom)] sm:hidden">
            <div className="grid grid-cols-4">
                {tabs.map((tab) => {
                    const active = tab.match(path);
                    const Icon = tab.icon;
                    return (
                        <Link
                            key={tab.label}
                            href={tab.href}
                            className={`flex flex-col items-center gap-0.5 py-2.5 text-[11px] font-medium ${active ? 'text-brand-700' : 'text-gray-500'}`}
                        >
                            <Icon className="h-6 w-6" strokeWidth={active ? 2.25 : 1.75} />
                            {tab.label}
                        </Link>
                    );
                })}
            </div>
        </nav>
    );
}
