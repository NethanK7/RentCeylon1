import { Head, Link, router } from '@inertiajs/react';
import { AlertTriangle, Eye, Pause, Play, Plus, Star, Trash2 } from 'lucide-react';
import SiteLayout from '@/Layouts/SiteLayout';
import { money } from '@/lib/format';

interface ListingRow {
    id: number; title: string; slug: string; status: string; status_label: string;
    daily_rate: number; currency: string; category: string; views: number;
    bookings_count: number; rating_avg: number; rating_count: number; photo: string | null;
}

const STATUS_STYLES: Record<string, string> = {
    active: 'bg-emerald-100 text-emerald-700',
    paused: 'bg-gray-200 text-gray-700',
    pending_verification: 'bg-amber-100 text-amber-700',
    removed: 'bg-rose-100 text-rose-700',
    draft: 'bg-gray-200 text-gray-700',
};

export default function Index({ listings, isIdVerified }: { listings: ListingRow[]; isIdVerified: boolean }) {
    const pause = (id: number) => router.post(route('lister.listings.pause', id));
    const activate = (id: number) => router.post(route('lister.listings.activate', id));
    const destroy = (id: number) => {
        if (confirm('Remove this listing? This cannot be undone.')) router.delete(route('lister.listings.destroy', id));
    };

    return (
        <SiteLayout>
            <Head title="My Listings" />
            <div className="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
                <div className="mb-6 flex items-center justify-between">
                    <h1 className="font-display text-2xl font-bold text-gray-900">My Listings</h1>
                    <Link href={route('lister.listings.create')} className="btn-primary"><Plus className="h-4 w-4" /> Add listing</Link>
                </div>

                {!isIdVerified && (
                    <div className="mb-6 flex items-center gap-3 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-amber-800">
                        <AlertTriangle className="h-6 w-6 flex-shrink-0" />
                        <p className="text-sm">Listings stay hidden from renters until your ID is verified. <Link href="/verify-id" className="font-semibold underline">Verify now</Link></p>
                    </div>
                )}

                {listings.length === 0 ? (
                    <div className="rounded-2xl border border-dashed border-gray-300 p-14 text-center text-gray-500">
                        <p>You haven't listed anything yet.</p>
                        <Link href={route('lister.listings.create')} className="btn-primary mt-4 inline-flex">Create your first listing</Link>
                    </div>
                ) : (
                    <div className="space-y-3">
                        {listings.map((l) => (
                            <div key={l.id} className="flex items-center gap-4 rounded-2xl border border-gray-200 p-4">
                                {l.photo ? <img src={l.photo} className="h-16 w-16 flex-shrink-0 rounded-xl object-cover" alt="" /> : <div className="h-16 w-16 flex-shrink-0 rounded-xl bg-gray-100" />}
                                <div className="min-w-0 flex-1">
                                    <div className="flex items-center gap-2">
                                        <p className="truncate font-semibold text-gray-900">{l.title}</p>
                                        <span className={`chip ${STATUS_STYLES[l.status] ?? 'bg-gray-100 text-gray-700'}`}>{l.status_label}</span>
                                    </div>
                                    <p className="text-sm text-gray-500">{l.category} · {money(l.daily_rate, l.currency)}/day</p>
                                    <div className="mt-1 flex items-center gap-3 text-xs text-gray-400">
                                        <span className="flex items-center gap-1"><Eye className="h-3.5 w-3.5" /> {l.views}</span>
                                        <span>{l.bookings_count} bookings</span>
                                        {l.rating_count > 0 && <span className="flex items-center gap-1"><Star className="h-3.5 w-3.5 fill-gray-900" /> {l.rating_avg.toFixed(1)}</span>}
                                    </div>
                                </div>
                                <div className="flex flex-shrink-0 items-center gap-2">
                                    <Link href={route('lister.listings.edit', l.id)} className="btn-outline px-3 py-2 text-xs">Edit</Link>
                                    {l.status === 'active' ? (
                                        <button onClick={() => pause(l.id)} className="btn-outline px-3 py-2 text-xs"><Pause className="h-3.5 w-3.5" /> Pause</button>
                                    ) : l.status === 'paused' ? (
                                        <button onClick={() => activate(l.id)} className="btn-outline px-3 py-2 text-xs"><Play className="h-3.5 w-3.5" /> Activate</button>
                                    ) : null}
                                    <button onClick={() => destroy(l.id)} className="rounded-xl p-2 text-gray-400 hover:bg-rose-50 hover:text-rose-600"><Trash2 className="h-4 w-4" /></button>
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </SiteLayout>
    );
}
