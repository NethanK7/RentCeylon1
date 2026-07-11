import AdminLayout from '@/Layouts/AdminLayout';
import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { Paginated } from '@/types/app';

interface Listing {
    id: number; title: string; slug: string; status: string;
    daily_rate: number; city: string; category: string;
    lister: { id: number; name: string };
    photo: string | null; created_at: string; deleted: boolean;
}

const statusBadge: Record<string, string> = {
    active: 'bg-green-100 text-green-700',
    paused: 'bg-yellow-100 text-yellow-700',
    pending_verification: 'bg-amber-100 text-amber-700',
    removed: 'bg-red-100 text-red-700',
};

function RemoveModal({ id, onClose }: { id: number; onClose: () => void }) {
    const [reason, setReason] = useState('');
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div className="bg-white rounded-2xl p-6 w-96 shadow-xl">
                <h3 className="font-bold text-gray-900 mb-3">Remove listing</h3>
                <textarea className="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm mb-4 h-24" placeholder="Reason for removal…" value={reason} onChange={e => setReason(e.target.value)} />
                <div className="flex gap-3">
                    <button onClick={onClose} className="flex-1 rounded-xl border border-gray-300 py-2 text-sm">Cancel</button>
                    <button disabled={!reason} onClick={() => { router.post(`/admin/listings/${id}/remove`, { reason }, { onSuccess: onClose }); }} className="flex-1 rounded-xl bg-red-600 text-white py-2 text-sm disabled:opacity-50">Remove</button>
                </div>
            </div>
        </div>
    );
}

export default function ListingsIndex({ listings, filters }: { listings: Paginated<Listing>; filters: Record<string, string> }) {
    const [q, setQ] = useState(filters.q ?? '');
    const [removeId, setRemoveId] = useState<number | null>(null);

    const search = () => router.get('/admin/listings', { q, status: filters.status }, { preserveState: true, replace: true });

    return (
        <AdminLayout title="Listings">
            <div className="flex gap-3 mb-5">
                <input className="rounded-xl border border-gray-300 px-3 py-2 text-sm flex-1 max-w-xs" placeholder="Search title…" value={q} onChange={e => setQ(e.target.value)} onKeyDown={e => e.key === 'Enter' && search()} />
                <select className="rounded-xl border border-gray-300 px-3 py-2 text-sm" value={filters.status ?? ''} onChange={e => router.get('/admin/listings', { q, status: e.target.value }, { preserveState: true, replace: true })}>
                    <option value="">All statuses</option>
                    <option value="active">Active</option>
                    <option value="paused">Paused</option>
                    <option value="pending_verification">Pending verification</option>
                    <option value="removed">Removed</option>
                </select>
                <button onClick={search} className="rounded-xl bg-gray-900 px-4 py-2 text-sm text-white">Search</button>
            </div>

            <div className="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">Listing</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">Lister</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">Category</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">Rate/day</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {listings.data.map((l) => (
                            <tr key={l.id} className="hover:bg-gray-50">
                                <td className="px-4 py-3">
                                    <div className="flex items-center gap-3">
                                        {l.photo && <img src={l.photo} className="h-9 w-9 rounded-lg object-cover flex-shrink-0" alt="" />}
                                        <div>
                                            <Link href={`/listings/${l.slug}`} target="_blank" className="font-medium text-gray-900 hover:text-blue-600 line-clamp-1">{l.title}</Link>
                                            <p className="text-xs text-gray-400">{l.city} · {l.created_at}</p>
                                        </div>
                                    </div>
                                </td>
                                <td className="px-4 py-3">
                                    <Link href={`/admin/users/${l.lister.id}`} className="text-gray-700 hover:text-blue-600">{l.lister.name}</Link>
                                </td>
                                <td className="px-4 py-3 text-gray-500">{l.category}</td>
                                <td className="px-4 py-3">
                                    <span className={`rounded-full px-2 py-0.5 text-xs font-medium capitalize ${statusBadge[l.status] ?? 'bg-gray-100 text-gray-600'}`}>{l.status.replace('_', ' ')}</span>
                                </td>
                                <td className="px-4 py-3 text-gray-700">LKR {l.daily_rate.toLocaleString()}</td>
                                <td className="px-4 py-3">
                                    {l.deleted
                                        ? <button onClick={() => router.post(`/admin/listings/${l.id}/restore`)} className="text-xs text-blue-600 hover:underline">Restore</button>
                                        : <button onClick={() => setRemoveId(l.id)} className="text-xs text-red-600 hover:underline">Remove</button>
                                    }
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {listings.last_page > 1 && (
                <div className="mt-4 flex gap-1">
                    {listings.links.map((l, i) => <Link key={i} href={l.url ?? '#'} className={`rounded-lg px-3 py-1.5 text-sm border ${l.active ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-200 text-gray-600 hover:bg-gray-50'}`} dangerouslySetInnerHTML={{ __html: l.label }} />)}
                </div>
            )}

            {removeId && <RemoveModal id={removeId} onClose={() => setRemoveId(null)} />}
        </AdminLayout>
    );
}
