import { Head, Link, router, useForm } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { CheckCircle2, Phone, ShieldCheck, Camera, FileText, Upload, XCircle } from 'lucide-react';
import SiteLayout from '@/Layouts/SiteLayout';
import { money } from '@/lib/format';

interface Booking {
    id: number; reference: string; status: string; status_label: string;
    start_date: string; end_date: string; days: number;
    subtotal: number; platform_fee: number; fee_rate: number; deposit_amount: number; total: number; currency: string;
    phone_revealed: boolean; deposit_status: string | null;
    listing: { title: string; photo: string | null };
    lister: { name: string; phone: string | null };
    is_renter: boolean;
    has_pickup_photos: boolean; has_return_photos: boolean;
    can_cancel: boolean;
    cancellation: { tier: string; rental_refund: number; deposit_refund: number; lister_compensation: number } | null;
}

function cancelTier(daysUntilStart: number) {
    if (daysUntilStart >= 7) return { tier: '7+ days before', refundPct: 100 };
    if (daysUntilStart >= 3) return { tier: '3–6 days before', refundPct: 50 };
    return { tier: 'Under 3 days before', refundPct: 0 };
}

export default function Show({ booking }: { booking: Booking }) {
    const [showCancel, setShowCancel] = useState(false);

    const daysUntilStart = useMemo(() => {
        const d = (new Date(booking.start_date).getTime() - new Date().setHours(0, 0, 0, 0)) / 86400000;
        return Math.ceil(d);
    }, [booking.start_date]);

    const preview = cancelTier(daysUntilStart);
    const previewRentalRefund = Math.round(booking.subtotal * (preview.refundPct / 100));
    const previewCompensation = preview.refundPct === 0 ? Math.round(booking.subtotal * 0.25) : 0;

    const pickupForm = useForm<{ phase: string; photo: File | null }>({ phase: 'pickup', photo: null });
    const returnForm = useForm<{ phase: string; photo: File | null }>({ phase: 'return', photo: null });

    const uploadPickup = (e: React.FormEvent) => {
        e.preventDefault();
        pickupForm.post(`/bookings/${booking.id}/photos`, { forceFormData: true });
    };
    const uploadReturn = (e: React.FormEvent) => {
        e.preventDefault();
        returnForm.post(`/bookings/${booking.id}/photos`, { forceFormData: true });
    };

    const cancelBooking = () => {
        router.post(`/bookings/${booking.id}/cancel`, {}, { onSuccess: () => setShowCancel(false) });
    };

    return (
        <SiteLayout>
            <Head title={`Booking ${booking.reference}`} />
            <div className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
                <StatusBanner booking={booking} />

                <div className="card overflow-hidden">
                    <div className="flex gap-4 border-b border-gray-200 p-5">
                        {booking.listing.photo && <img src={booking.listing.photo} className="h-20 w-20 rounded-xl object-cover" alt="" />}
                        <div>
                            <p className="text-lg font-bold text-gray-900">{booking.listing.title}</p>
                            <p className="text-sm text-gray-500">{booking.start_date} → {booking.end_date} · {booking.days} days</p>
                        </div>
                    </div>

                    <div className="space-y-2 p-5 text-sm">
                        <Row label="Rental subtotal" value={money(booking.subtotal, booking.currency)} />
                        <Row label={`Platform fee (${Math.round(booking.fee_rate * 100)}%)`} value={money(booking.platform_fee, booking.currency)} />
                        <Row label="Deposit (in escrow)" value={money(booking.deposit_amount, booking.currency)} muted />
                        <div className="border-t border-gray-200 pt-2"><Row label="Total paid" value={money(booking.total, booking.currency)} bold /></div>
                    </div>

                    <div className="border-t border-gray-200 p-5">
                        <p className="mb-2 font-semibold text-gray-900">Lister contact</p>
                        {booking.phone_revealed ? (
                            <p className="flex items-center gap-2 text-gray-700"><Phone className="h-4 w-4 text-ceylon-600" /> {booking.lister.name} · {booking.lister.phone ?? '—'}</p>
                        ) : (
                            <p className="text-sm text-gray-500">Contact is revealed once payment is confirmed.</p>
                        )}
                    </div>

                    {/* Condition photo gate (Constraint 02) */}
                    <div className="grid gap-3 border-t border-gray-200 p-5 sm:grid-cols-2">
                        <GateItem done={booking.has_pickup_photos} icon={Camera} label="Pickup condition photos" />
                        <GateItem done={booking.has_return_photos} icon={Camera} label="Return condition photos" />
                    </div>

                    {/* Renter actions: upload pickup / return photos */}
                    {booking.is_renter && booking.status === 'confirmed' && (
                        <UploadSection
                            title="Upload pickup photo to start your rental"
                            form={pickupForm}
                            onSubmit={uploadPickup}
                            cta="Confirm pickup"
                        />
                    )}
                    {booking.is_renter && booking.status === 'active' && (
                        <UploadSection
                            title="Returning the item? Upload a return photo"
                            form={returnForm}
                            onSubmit={uploadReturn}
                            cta="Confirm return"
                        />
                    )}
                    {booking.status === 'returned' && (
                        <div className="border-t border-gray-200 bg-gold-50 p-5 text-sm text-gold-800">
                            Return photo received — waiting for the lister to confirm and release the deposit.
                        </div>
                    )}
                    {booking.status === 'closed' && (
                        <div className="flex items-center gap-2 border-t border-gray-200 bg-emerald-50 p-5 text-sm text-emerald-800">
                            <CheckCircle2 className="h-5 w-5" /> Completed — deposit released.
                        </div>
                    )}

                    {/* Cancellation (Page 23) */}
                    {booking.is_renter && booking.can_cancel && (
                        <div className="border-t border-gray-200 p-5">
                            {!showCancel ? (
                                <button onClick={() => setShowCancel(true)} className="text-sm font-semibold text-rose-600 hover:underline">
                                    Cancel this booking
                                </button>
                            ) : (
                                <div className="rounded-xl border border-rose-200 bg-rose-50 p-4">
                                    <p className="flex items-center gap-2 font-semibold text-rose-800"><XCircle className="h-4 w-4" /> Cancel booking — {preview.tier}</p>
                                    <ul className="mt-2 space-y-1 text-sm text-rose-700">
                                        <li>Rental refund: {money(previewRentalRefund, booking.currency)} ({preview.refundPct}%)</li>
                                        <li>Deposit refund: {money(booking.deposit_amount, booking.currency)} (always returned)</li>
                                        {previewCompensation > 0 && <li>Lister compensation (late cancel): {money(previewCompensation, booking.currency)}</li>}
                                    </ul>
                                    <div className="mt-3 flex gap-2">
                                        <button onClick={cancelBooking} className="btn bg-rose-600 text-white hover:bg-rose-700">Confirm cancellation</button>
                                        <button onClick={() => setShowCancel(false)} className="btn-outline">Never mind</button>
                                    </div>
                                </div>
                            )}
                        </div>
                    )}

                    {booking.cancellation && (
                        <div className="border-t border-gray-200 bg-rose-50 p-5 text-sm text-rose-800">
                            <p className="font-semibold">Booking cancelled</p>
                            <p>Rental refund: {money(booking.cancellation.rental_refund, booking.currency)} · Deposit refund: {money(booking.cancellation.deposit_refund, booking.currency)}</p>
                            {booking.cancellation.lister_compensation > 0 && <p>Lister compensation: {money(booking.cancellation.lister_compensation, booking.currency)}</p>}
                        </div>
                    )}

                    <div className="flex flex-wrap items-center gap-3 border-t border-gray-200 p-5">
                        <button className="btn-outline"><FileText className="h-4 w-4" /> Download rental agreement</button>
                        <span className="inline-flex items-center gap-1.5 text-sm text-gray-500"><ShieldCheck className="h-4 w-4 text-ceylon-600" /> Deposit status: {booking.deposit_status ?? '—'}</span>
                    </div>
                </div>

                <div className="mt-6 text-center">
                    <Link href="/dashboard" className="text-sm font-semibold text-brand-700 hover:underline">Go to my dashboard →</Link>
                </div>
            </div>
        </SiteLayout>
    );
}

function StatusBanner({ booking }: { booking: Booking }) {
    const ok = ['confirmed', 'active', 'returned', 'closed'].includes(booking.status);
    return (
        <div className={`mb-6 flex items-center gap-3 rounded-2xl p-5 ${ok ? 'bg-emerald-50 text-emerald-800' : 'bg-rose-50 text-rose-800'}`}>
            {ok ? <CheckCircle2 className="h-8 w-8" /> : <XCircle className="h-8 w-8" />}
            <div>
                <p className="font-bold">{booking.reference}</p>
                <p className="text-sm">Status: {booking.status_label}</p>
            </div>
        </div>
    );
}

function UploadSection({ title, form, onSubmit, cta }: {
    title: string; form: any; onSubmit: (e: React.FormEvent) => void; cta: string;
}) {
    return (
        <form onSubmit={onSubmit} className="space-y-3 border-t border-gray-200 p-5">
            <p className="font-semibold text-gray-900">{title}</p>
            <label className="flex cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-300 py-6 text-sm text-gray-500 hover:border-gray-400">
                <Upload className="h-5 w-5" />
                {form.data.photo ? form.data.photo.name : 'Tap to choose a photo'}
                <input type="file" accept="image/*" className="hidden" onChange={(e) => form.setData('photo', e.target.files?.[0] ?? null)} />
            </label>
            {form.errors.photo && <p className="text-sm text-rose-600">{form.errors.photo}</p>}
            <button type="submit" disabled={!form.data.photo || form.processing} className="btn-primary w-full">
                {form.processing ? 'Uploading…' : cta}
            </button>
        </form>
    );
}

function GateItem({ done, icon: I, label }: { done: boolean; icon: any; label: string }) {
    return (
        <div className={`flex items-center gap-2 rounded-xl border p-3 ${done ? 'border-emerald-300 bg-emerald-50 text-emerald-800' : 'border-gray-200 text-gray-500'}`}>
            <I className="h-5 w-5" />
            <span className="text-sm font-medium">{label}</span>
            <span className="ml-auto text-xs">{done ? 'Uploaded' : 'Pending'}</span>
        </div>
    );
}

function Row({ label, value, bold, muted }: { label: string; value: string; bold?: boolean; muted?: boolean }) {
    return <div className={`flex justify-between ${bold ? 'font-bold text-gray-900' : muted ? 'text-gray-500' : 'text-gray-700'}`}><span>{label}</span><span>{value}</span></div>;
}
