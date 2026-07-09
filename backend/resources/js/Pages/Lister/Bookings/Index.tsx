import { Head, router } from '@inertiajs/react';
import { Camera, Check, Phone } from 'lucide-react';
import SiteLayout from '@/Layouts/SiteLayout';
import { money } from '@/lib/format';

interface BookingRow {
    id: number; reference: string; status: string; status_label: string;
    start_date: string; end_date: string; total: number; deposit_amount: number;
    deposit_status: string | null; currency: string;
    renter: { name: string; phone: string | null };
    listing: { title: string; photo: string | null };
    has_pickup_photos: boolean; has_return_photos: boolean; can_confirm_return: boolean;
}

const STATUS_STYLES: Record<string, string> = {
    pending_confirmation: 'bg-amber-100 text-amber-700',
    confirmed: 'bg-blue-100 text-blue-700',
    active: 'bg-emerald-100 text-emerald-700',
    awaiting_return: 'bg-amber-100 text-amber-700',
    returned: 'bg-gold-100 text-gold-700',
    completed: 'bg-gray-200 text-gray-700',
    closed: 'bg-gray-200 text-gray-700',
    disputed: 'bg-rose-100 text-rose-700',
};

export default function Index({ bookings }: { bookings: BookingRow[] }) {
    const confirmReturn = (id: number) => {
        if (confirm('Confirm the item was returned in good condition? This releases the deposit to you.')) {
            router.post(route('lister.bookings.confirm-return', id));
        }
    };

    return (
        <SiteLayout>
            <Head title="Incoming Bookings" />
            <div className="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
                <h1 className="font-display text-2xl font-bold text-gray-900">Incoming Bookings</h1>
                <p className="mt-1 text-gray-500">Deposits are held in escrow and only release when you confirm a return.</p>

                {bookings.length === 0 ? (
                    <div className="mt-6 rounded-2xl border border-dashed border-gray-300 p-14 text-center text-gray-500">No bookings yet.</div>
                ) : (
                    <div className="mt-6 space-y-3">
                        {bookings.map((b) => (
                            <div key={b.id} className="rounded-2xl border border-gray-200 p-4">
                                <div className="flex items-center gap-4">
                                    {b.listing.photo ? <img src={b.listing.photo} className="h-16 w-16 flex-shrink-0 rounded-xl object-cover" alt="" /> : <div className="h-16 w-16 flex-shrink-0 rounded-xl bg-gray-100" />}
                                    <div className="min-w-0 flex-1">
                                        <div className="flex flex-wrap items-center gap-2">
                                            <p className="font-semibold text-gray-900">{b.listing.title}</p>
                                            <span className={`chip ${STATUS_STYLES[b.status] ?? 'bg-gray-100 text-gray-700'}`}>{b.status_label}</span>
                                        </div>
                                        <p className="text-sm text-gray-500">{b.reference} · {b.start_date} → {b.end_date} · {b.renter.name}</p>
                                        {b.renter.phone && (
                                            <p className="mt-0.5 flex items-center gap-1 text-sm text-gray-600"><Phone className="h-3.5 w-3.5" /> {b.renter.phone}</p>
                                        )}
                                    </div>
                                    <div className="flex-shrink-0 text-right">
                                        <p className="font-semibold text-gray-900">{money(b.total, b.currency)}</p>
                                        <p className="text-xs text-gray-500">Deposit: {money(b.deposit_amount, b.currency)} · {b.deposit_status ?? '—'}</p>
                                    </div>
                                </div>

                                <div className="mt-3 flex flex-wrap items-center gap-3 border-t border-gray-100 pt-3">
                                    <span className={`flex items-center gap-1 text-xs ${b.has_pickup_photos ? 'text-emerald-600' : 'text-gray-400'}`}>
                                        <Camera className="h-3.5 w-3.5" /> Pickup photo {b.has_pickup_photos ? 'done' : 'pending'}
                                    </span>
                                    <span className={`flex items-center gap-1 text-xs ${b.has_return_photos ? 'text-emerald-600' : 'text-gray-400'}`}>
                                        <Camera className="h-3.5 w-3.5" /> Return photo {b.has_return_photos ? 'done' : 'pending'}
                                    </span>

                                    {b.can_confirm_return && (
                                        <button onClick={() => confirmReturn(b.id)} className="btn-primary ml-auto px-3 py-2 text-xs">
                                            <Check className="h-3.5 w-3.5" /> Confirm return & release deposit
                                        </button>
                                    )}
                                </div>
                            </div>
                        ))}
                    </div>
                )}
            </div>
        </SiteLayout>
    );
}
