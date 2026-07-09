import { Head, useForm } from '@inertiajs/react';
import { ShieldCheck, Lock } from 'lucide-react';
import SiteLayout from '@/Layouts/SiteLayout';
import { money } from '@/lib/format';

interface Quote { daily_rate: number; days: number; subtotal: number; fee_rate: number; fee_rate_label: string; platform_fee: number; deposit: number; total: number; start: string; end: string; }

export default function Checkout({ listing, quote, idempotencyKey }: {
    listing: { id: number; title: string; slug: string; city: string; currency: string; photo: string | null; lister: string };
    quote: Quote;
    idempotencyKey: string;
}) {
    const { data, setData, post, processing, errors } = useForm({
        start: quote.start,
        end: quote.end,
        gateway: 'payhere',
        idempotency_key: idempotencyKey,
        accept_policy: false as boolean,
        accept_agreement: false as boolean,
    });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(`/listings/${listing.slug}/checkout`);
    };

    return (
        <SiteLayout>
            <Head title={`Checkout · ${listing.title}`} />
            <div className="mx-auto max-w-5xl px-4 py-8 sm:px-6 lg:px-8">
                <h1 className="mb-6 font-display text-2xl font-bold text-gray-900">Confirm and pay</h1>

                <form onSubmit={submit} className="grid gap-8 lg:grid-cols-5">
                    <div className="space-y-6 lg:col-span-3">
                        {/* Dates */}
                        <section className="card p-5">
                            <h2 className="mb-3 font-bold text-gray-900">Your rental dates</h2>
                            <div className="grid grid-cols-2 gap-3">
                                <div><label className="text-xs font-semibold uppercase text-gray-500">Start</label>
                                    <input type="date" className="input mt-1" value={data.start} onChange={(e) => setData('start', e.target.value)} /></div>
                                <div><label className="text-xs font-semibold uppercase text-gray-500">End</label>
                                    <input type="date" className="input mt-1" value={data.end} onChange={(e) => setData('end', e.target.value)} /></div>
                            </div>
                            {(errors as any).dates && <p className="mt-2 text-sm text-rose-600">{(errors as any).dates}</p>}
                        </section>

                        {/* Payment method */}
                        <section className="card p-5">
                            <h2 className="mb-3 font-bold text-gray-900">Payment method</h2>
                            <div className="space-y-2">
                                {[['payhere', 'PayHere', 'Cards, eZ Cash, bank'], ['ipay', 'iPay', 'iPay wallet & cards'], ['stripe', 'Stripe', 'International cards']].map(([val, name, desc]) => (
                                    <label key={val} className={`flex cursor-pointer items-center gap-3 rounded-xl border p-3 ${data.gateway === val ? 'border-gray-900 ring-1 ring-gray-900' : 'border-gray-300'}`}>
                                        <input type="radio" name="gateway" value={val} checked={data.gateway === val} onChange={(e) => setData('gateway', e.target.value)} className="text-brand-500 focus:ring-brand-500" />
                                        <span><span className="font-semibold text-gray-900">{name}</span> <span className="text-sm text-gray-500">· {desc}</span></span>
                                    </label>
                                ))}
                            </div>
                            <p className="mt-3 flex items-center gap-1.5 text-xs text-gray-500"><Lock className="h-3.5 w-3.5" /> Tokenised & encrypted. No card data is stored. Idempotency-protected against double charges.</p>
                        </section>

                        {/* Policies — must accept before payment */}
                        <section className="card space-y-3 p-5">
                            <h2 className="font-bold text-gray-900">Before you pay</h2>
                            <label className="flex items-start gap-2 text-sm text-gray-700">
                                <input type="checkbox" checked={data.accept_policy} onChange={(e) => setData('accept_policy', e.target.checked)} className="mt-0.5 rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                                <span>I accept the <a href="/trust" className="font-semibold underline">cancellation policy</a>: full refund 7+ days before, 50% at 3–6 days, deposit always returned unless no-show.</span>
                            </label>
                            <label className="flex items-start gap-2 text-sm text-gray-700">
                                <input type="checkbox" checked={data.accept_agreement} onChange={(e) => setData('accept_agreement', e.target.checked)} className="mt-0.5 rounded border-gray-300 text-brand-500 focus:ring-brand-500" />
                                <span>I have read and accept the <a href="/trust" className="font-semibold underline">rental agreement</a> for this booking.</span>
                            </label>
                            {(errors.accept_policy || errors.accept_agreement || (errors as any).agreement) && (
                                <p className="text-sm text-rose-600">{errors.accept_policy || errors.accept_agreement || (errors as any).agreement}</p>
                            )}
                        </section>
                    </div>

                    {/* Summary */}
                    <div className="lg:col-span-2">
                        <div className="sticky top-24 card p-5">
                            <div className="flex gap-3 border-b border-gray-200 pb-4">
                                {listing.photo && <img src={listing.photo} className="h-16 w-16 rounded-xl object-cover" alt="" />}
                                <div><p className="font-semibold text-gray-900">{listing.title}</p><p className="text-sm text-gray-500">{listing.city} · {listing.lister}</p></div>
                            </div>
                            <div className="space-y-2 py-4 text-sm">
                                <Row label={`${money(quote.daily_rate, listing.currency)} × ${quote.days} day${quote.days > 1 ? 's' : ''}`} value={money(quote.subtotal, listing.currency)} />
                                <Row label={`Platform fee (${quote.fee_rate_label})`} value={money(quote.platform_fee, listing.currency)} />
                                <Row label="Refundable deposit" value={money(quote.deposit, listing.currency)} muted />
                            </div>
                            <div className="border-t border-gray-200 pt-3">
                                <Row label="Total due now" value={money(quote.total, listing.currency)} bold />
                            </div>
                            <button type="submit" disabled={processing} className="btn-primary mt-4 w-full">
                                {processing ? 'Processing…' : `Pay ${money(quote.total, listing.currency)}`}
                            </button>
                            <p className="mt-3 flex items-start gap-1.5 text-xs text-gray-500">
                                <ShieldCheck className="mt-0.5 h-4 w-4 flex-shrink-0 text-ceylon-600" />
                                Your {money(quote.deposit, listing.currency)} deposit is held in escrow and released after both parties confirm the return.
                            </p>
                        </div>
                    </div>
                </form>
            </div>
        </SiteLayout>
    );
}

function Row({ label, value, bold, muted }: { label: string; value: string; bold?: boolean; muted?: boolean }) {
    return <div className={`flex justify-between ${bold ? 'font-bold text-gray-900' : muted ? 'text-gray-500' : 'text-gray-700'}`}><span>{label}</span><span>{value}</span></div>;
}
