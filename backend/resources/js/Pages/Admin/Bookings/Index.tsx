import AdminLayout from '@/Layouts/AdminLayout';
import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { Paginated } from '@/types/app';

interface Booking {
    id: number; reference: string; status: string;
    listing: { title: string; slug: string };
    renter: string; lister: string;
    start_date: string; end_date: string;
    total: number; currency: string; created_at: string;
}

const statusBadge: Record<string, string> = {
    active: 'bg-green-100 text-green-700',
    confirmed: 'bg-blue-100 text-blue-700',
    pending_confirmation: 'bg-amber-100 text-amber-700',
    disputed: 'bg-red-100 text-red-700',
    completed: 'bg-gray-100 text-gray-600',
    closed: 'bg-gray-100 text-gray-400',
    cancelled: 'bg-red-50 text-red-400',
    no_show: 'bg-red-50 text-red-400',
    awaiting_return: 'bg-purple-100 text-purple-700',
    returned: 'bg-teal-100 text-teal-700',
};

export default function BookingsIndex({ bookings, filters }: { bookings: Paginated<Booking>; filters: Record<string, string> }) {
    const [q, setQ] = useState(filters.q ?? '');

    const search = () => router.get('/admin/bookings', { q, status: filters.status }, { preserveState: true, replace: true });

    return (
        <AdminLayout title="Bookings">
            <div className="flex gap-3 mb-5">
                <input className="rounded-xl border border-gray-300 px-3 py-2 text-sm flex-1 max-w-xs" placeholder="Search reference…" value={q} onChange={e => setQ(e.target.value)} onKeyDown={e => e.key === 'Enter' && search()} />
                <select className="rounded-xl border border-gray-300 px-3 py-2 text-sm" value={filters.status ?? ''} onChange={e => router.get('/admin/bookings', { q, status: e.target.value }, { preserveState: true, replace: true })}>
                    <option value="">All statuses</option>
                    <option value="pending_confirmation">Pending confirmation</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="active">Active</option>
                    <option value="awaiting_return">Awaiting return</option>
                    <option value="disputed">Disputed</option>
                    <option value="completed">Completed</option>
                    <option value="closed">Closed</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>

            <div className="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">Reference</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">Listing</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">Renter</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">Lister</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">Dates</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">Total</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {bookings.data.map((b) => (
                            <tr key={b.id} className="hover:bg-gray-50">
                                <td className="px-4 py-3 font-mono text-xs text-gray-600">{b.reference}</td>
                                <td className="px-4 py-3">
                                    <Link href={`/listings/${b.listing.slug}`} target="_blank" className="text-gray-900 hover:text-blue-600 line-clamp-1">{b.listing.title}</Link>
                                </td>
                                <td className="px-4 py-3 text-gray-600">{b.renter}</td>
                                <td className="px-4 py-3 text-gray-600">{b.lister}</td>
                                <td className="px-4 py-3 text-gray-500 text-xs">{b.start_date} → {b.end_date}</td>
                                <td className="px-4 py-3 font-medium">{b.currency} {b.total.toLocaleString()}</td>
                                <td className="px-4 py-3">
                                    <span className={`rounded-full px-2 py-0.5 text-xs font-medium ${statusBadge[b.status] ?? 'bg-gray-100 text-gray-600'}`}>{b.status.replace(/_/g, ' ')}</span>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {bookings.last_page > 1 && (
                <div className="mt-4 flex gap-1">
                    {bookings.links.map((l, i) => <Link key={i} href={l.url ?? '#'} className={`rounded-lg px-3 py-1.5 text-sm border ${l.active ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-200 text-gray-600 hover:bg-gray-50'}`} dangerouslySetInnerHTML={{ __html: l.label }} />)}
                </div>
            )}
        </AdminLayout>
    );
}
