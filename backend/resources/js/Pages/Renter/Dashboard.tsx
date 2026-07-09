import { Head, Link } from '@inertiajs/react';
import DashboardShell from '@/Components/site/DashboardShell';
import { money } from '@/lib/format';

interface BookingRow {
    id: number; reference: string; status: string; status_label: string;
    start_date: string; end_date: string; total: number; currency: string;
    listing: { title: string; photo: string | null };
    lister: string;
}

const STATUS_STYLES: Record<string, string> = {
    pending_confirmation: 'bg-amber-100 text-amber-700',
    confirmed: 'bg-blue-100 text-blue-700',
    active: 'bg-emerald-100 text-emerald-700',
    awaiting_return: 'bg-amber-100 text-amber-700',
    returned: 'bg-gold-100 text-gold-700',
    closed: 'bg-gray-200 text-gray-700',
    cancelled: 'bg-rose-100 text-rose-700',
    no_show: 'bg-rose-100 text-rose-700',
    disputed: 'bg-rose-100 text-rose-700',
};

function BookingRowCard({ b }: { b: BookingRow }) {
    return (
        <Link href={`/bookings/${b.id}`} className="flex items-center gap-4 rounded-2xl border border-gray-200 p-4 transition hover:shadow-card">
            {b.listing.photo ? <img src={b.listing.photo} className="h-16 w-16 flex-shrink-0 rounded-xl object-cover" alt="" /> : <div className="h-16 w-16 flex-shrink-0 rounded-xl bg-gray-100" />}
            <div className="min-w-0 flex-1">
                <div className="flex flex-wrap items-center gap-2">
                    <p className="font-semibold text-gray-900">{b.listing.title}</p>
                    <span className={`chip ${STATUS_STYLES[b.status] ?? 'bg-gray-100 text-gray-700'}`}>{b.status_label}</span>
                </div>
                <p className="text-sm text-gray-500">{b.reference} · {b.start_date} → {b.end_date} · {b.lister}</p>
            </div>
            <p className="flex-shrink-0 font-semibold text-gray-900">{money(b.total, b.currency)}</p>
        </Link>
    );
}

export default function Dashboard({ active, history }: { active: BookingRow[]; history: BookingRow[] }) {
    return (
        <DashboardShell title="My rentals" subtitle="Track active bookings, returns and history.">
            <Head title="My rentals" />

            <h2 className="mb-3 font-semibold text-gray-900">Active</h2>
            {active.length === 0 ? (
                <div className="rounded-2xl border border-dashed border-gray-300 p-10 text-center text-gray-500">
                    <p>You have no active rentals yet.</p>
                    <Link href="/browse" className="btn-primary mt-4 inline-flex">Browse rentals</Link>
                </div>
            ) : (
                <div className="space-y-3">{active.map((b) => <BookingRowCard key={b.id} b={b} />)}</div>
            )}

            {history.length > 0 && (
                <>
                    <h2 className="mb-3 mt-8 font-semibold text-gray-900">History</h2>
                    <div className="space-y-3">{history.map((b) => <BookingRowCard key={b.id} b={b} />)}</div>
                </>
            )}
        </DashboardShell>
    );
}
