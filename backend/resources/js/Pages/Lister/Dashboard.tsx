import { Link, usePage } from '@inertiajs/react';
import { AlertTriangle } from 'lucide-react';
import DashboardShell, { StatCard } from '@/Components/site/DashboardShell';
import { PageProps } from '@/types/app';
import { money } from '@/lib/format';

interface Stats { active_listings: number; booking_requests: number; pending_earnings: number; paid_out: number; }

export default function Dashboard({ stats }: { stats: Stats }) {
    const { auth } = usePage<PageProps>().props;
    const notVerified = auth.user && !auth.user.is_id_verified;

    return (
        <DashboardShell title="Lister dashboard" subtitle="Your listings, bookings and earnings at a glance.">
            {notVerified && (
                <div className="mb-6 flex items-center gap-3 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-amber-800">
                    <AlertTriangle className="h-6 w-6" />
                    <div className="flex-1">
                        <p className="font-semibold">ID verification required</p>
                        <p className="text-sm">Your listings stay hidden until your ID is approved.</p>
                    </div>
                    <Link href="/verify-id" className="btn bg-amber-500 text-white hover:bg-amber-600">Verify now</Link>
                </div>
            )}

            <div className="grid gap-4 sm:grid-cols-4">
                <StatCard label="Active listings" value={String(stats.active_listings)} />
                <StatCard label="Booking requests" value={String(stats.booking_requests)} />
                <StatCard label="Pending earnings" value={money(stats.pending_earnings)} />
                <StatCard label="Paid out" value={money(stats.paid_out)} />
            </div>

            <div className="mt-6 grid gap-4 sm:grid-cols-3">
                <Action title="Add a listing" desc="Create a new item to rent out." href={route('lister.listings.create')} />
                <Action title="My listings" desc="Manage, pause or edit your items." href={route('lister.listings.index')} />
                <Action title="Incoming bookings" desc="Confirm returns & release deposits." href={route('lister.bookings.index')} />
            </div>
        </DashboardShell>
    );
}

function Action({ title, desc, href }: { title: string; desc: string; href: string }) {
    return (
        <Link href={href} className="rounded-2xl border border-gray-200 p-5 transition hover:border-brand-800 hover:shadow-card">
            <p className="font-semibold text-gray-900">{title}</p>
            <p className="text-sm text-gray-500">{desc}</p>
        </Link>
    );
}
