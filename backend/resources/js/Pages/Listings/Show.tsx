import { Head, Link, router, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { MapPin, Share2, ShieldCheck, Star, Info } from 'lucide-react';
import SiteLayout from '@/Layouts/SiteLayout';
import Stars from '@/Components/site/Stars';
import { EarnedZone, PromotedZone } from '@/Components/site/Badges';
import { money } from '@/lib/format';
import { BadgeChip, PageProps } from '@/types/app';

interface Listing {
    id: number; title: string; slug: string; description: string; condition: string;
    daily_rate: number; security_deposit: number; currency: string;
    city: string; district: string; rating_avg: number; rating_count: number; views: number;
    photos: { url: string }[];
    category: { name: string; slug: string; kind: string };
    attributes: { label: string; value: string; unit: string | null }[];
    earnedBadges: BadgeChip[]; promotedBadges: BadgeChip[];
    lister: { id: number; name: string; city: string; rating_avg: number; rating_count: number; member_since: string; badges: BadgeChip[] };
    reviews: { rating: number; body: string; author: string; date: string }[];
}

// Mirror of server PlatformFee (Amendment 08) for live preview.
function feeRate(subtotal: number) { return subtotal <= 10000 ? 0.1 : subtotal <= 50000 ? 0.07 : 0.05; }

export default function Show({ listing }: { listing: Listing }) {
    const { auth } = usePage<PageProps>().props;
    const [active, setActive] = useState(0);
    const [start, setStart] = useState('');
    const [end, setEnd] = useState('');

    const days = useMemo(() => {
        if (!start || !end) return 0;
        const d = (new Date(end).getTime() - new Date(start).getTime()) / 86400000;
        return d >= 0 ? Math.floor(d) + 1 : 0;
    }, [start, end]);

    const quote = useMemo(() => {
        const subtotal = listing.daily_rate * (days || 1);
        const rate = feeRate(subtotal);
        const fee = Math.round(subtotal * rate);
        return { subtotal, rate, fee, deposit: listing.security_deposit, total: subtotal + fee + listing.security_deposit };
    }, [days, listing]);

    const goCheckout = () => {
        if (!auth.user) { router.visit(`/login`); return; }
        if (!start || !end || days < 1) return;
        router.get(`/listings/${listing.slug}/checkout`, { start, end });
    };

    const share = async () => {
        const url = window.location.href;
        try { await navigator.share?.({ title: listing.title, url }); }
        catch { navigator.clipboard?.writeText(url); }
    };

    return (
        <SiteLayout>
            <Head title={listing.title} />
            <div className="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
                {/* Title row */}
                <div className="mb-4 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h1 className="font-display text-2xl font-bold text-gray-900 sm:text-3xl">{listing.title}</h1>
                        <div className="mt-1 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-gray-600">
                            {listing.rating_count > 0 && <Stars rating={listing.rating_avg} count={listing.rating_count} />}
                            <span className="inline-flex items-center gap-1"><MapPin className="h-4 w-4" />{listing.city}, {listing.district}</span>
                            <span className="capitalize text-gray-500">{listing.condition.replace('_', ' ')} condition</span>
                        </div>
                    </div>
                    <button onClick={share} className="btn-outline"><Share2 className="h-4 w-4" /> Share</button>
                </div>

                {/* Gallery */}
                <div className="grid gap-2 overflow-hidden rounded-2xl sm:grid-cols-4 sm:grid-rows-2" style={{ maxHeight: 460 }}>
                    <div className="sm:col-span-2 sm:row-span-2">
                        <img src={listing.photos[active]?.url} alt={listing.title} className="h-full w-full object-cover" style={{ minHeight: 300 }} />
                    </div>
                    {listing.photos.slice(0, 4).map((p, i) => (
                        <button key={i} onClick={() => setActive(i)} className="hidden sm:block">
                            <img src={p.url} alt="" className={`h-full w-full object-cover ${active === i ? 'ring-2 ring-brand-500' : ''}`} />
                        </button>
                    ))}
                </div>

                <div className="mt-8 grid gap-10 lg:grid-cols-3">
                    {/* Left: details */}
                    <div className="space-y-8 lg:col-span-2">
                        {/* Lister + badges */}
                        <div className="flex items-start justify-between gap-4 border-b border-gray-200 pb-6">
                            <div className="flex items-center gap-3">
                                <div className="flex h-12 w-12 items-center justify-center rounded-full bg-gray-900 text-lg font-semibold text-white">{listing.lister.name.charAt(0)}</div>
                                <div>
                                    <p className="font-semibold text-gray-900">Listed by {listing.lister.name}</p>
                                    <p className="text-sm text-gray-500">Member since {listing.lister.member_since} · {listing.lister.city}</p>
                                </div>
                            </div>
                        </div>

                        {/* Badge zones — kept spatially separate (Constraint 01) */}
                        {(listing.earnedBadges.length > 0 || listing.promotedBadges.length > 0) && (
                            <div className="flex flex-wrap gap-8">
                                <EarnedZone badges={listing.earnedBadges} />
                                <PromotedZone badges={listing.promotedBadges} />
                            </div>
                        )}

                        {/* Specs / attributes */}
                        {listing.attributes.length > 0 && (
                            <div>
                                <h2 className="mb-3 text-lg font-bold text-gray-900">Details</h2>
                                <dl className="grid grid-cols-2 gap-3 sm:grid-cols-3">
                                    {listing.attributes.map((a, i) => (
                                        <div key={i} className="rounded-xl border border-gray-200 p-3">
                                            <dt className="text-xs text-gray-500">{a.label}</dt>
                                            <dd className="font-semibold text-gray-900">{a.value}{a.unit ? ` ${a.unit}` : ''}</dd>
                                        </div>
                                    ))}
                                </dl>
                            </div>
                        )}

                        {/* Description */}
                        <div>
                            <h2 className="mb-2 text-lg font-bold text-gray-900">About this item</h2>
                            <p className="whitespace-pre-line text-gray-700">{listing.description}</p>
                        </div>

                        {/* Reviews */}
                        <div>
                            <h2 className="mb-4 flex items-center gap-2 text-lg font-bold text-gray-900">
                                <Star className="h-5 w-5 fill-gray-900" /> {Number(listing.rating_avg).toFixed(1)} · {listing.rating_count} reviews
                            </h2>
                            {listing.reviews.length === 0 ? (
                                <p className="text-gray-500">No reviews yet.</p>
                            ) : (
                                <div className="grid gap-5 sm:grid-cols-2">
                                    {listing.reviews.map((r, i) => (
                                        <div key={i}>
                                            <div className="flex items-center gap-2">
                                                <div className="flex h-8 w-8 items-center justify-center rounded-full bg-gray-200 text-sm font-semibold">{r.author.charAt(0)}</div>
                                                <div>
                                                    <p className="text-sm font-semibold text-gray-900">{r.author}</p>
                                                    <p className="text-xs text-gray-500">{r.date}</p>
                                                </div>
                                            </div>
                                            <p className="mt-2 text-sm text-gray-700">{r.body}</p>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Right: sticky booking card */}
                    <div className="lg:col-span-1">
                        <div className="sticky top-24 rounded-2xl border border-gray-200 p-5 shadow-card">
                            <div className="flex items-baseline justify-between">
                                <p className="text-2xl font-bold text-gray-900">{money(listing.daily_rate, listing.currency)}<span className="text-base font-normal text-gray-500"> / day</span></p>
                                {listing.rating_count > 0 && <Stars rating={listing.rating_avg} count={listing.rating_count} />}
                            </div>

                            <div className="mt-4 grid grid-cols-2 overflow-hidden rounded-xl border border-gray-300">
                                <label className="border-r border-gray-300 p-2.5">
                                    <span className="block text-[11px] font-semibold uppercase text-gray-500">Start</span>
                                    <input type="date" value={start} min={new Date().toISOString().slice(0, 10)} onChange={(e) => setStart(e.target.value)} className="w-full border-0 p-0 text-sm focus:ring-0" />
                                </label>
                                <label className="p-2.5">
                                    <span className="block text-[11px] font-semibold uppercase text-gray-500">End</span>
                                    <input type="date" value={end} min={start || new Date().toISOString().slice(0, 10)} onChange={(e) => setEnd(e.target.value)} className="w-full border-0 p-0 text-sm focus:ring-0" />
                                </label>
                            </div>

                            {/* Transparent fee breakdown BEFORE checkout (Constraint / Page 03) */}
                            <div className="mt-4 space-y-2 text-sm">
                                <Row label={`${money(listing.daily_rate, listing.currency)} × ${days || 1} day${(days || 1) > 1 ? 's' : ''}`} value={money(quote.subtotal, listing.currency)} />
                                <Row label={`Platform fee (${Math.round(quote.rate * 100)}%)`} value={money(quote.fee, listing.currency)} />
                                <Row label="Refundable deposit" value={money(quote.deposit, listing.currency)} muted />
                                <div className="border-t border-gray-200 pt-2">
                                    <Row label="Total due now" value={money(quote.total, listing.currency)} bold />
                                </div>
                            </div>

                            <button onClick={goCheckout} disabled={!start || !end || days < 1}
                                className="btn-primary mt-4 w-full">
                                {auth.user ? 'Reserve' : 'Log in to book'}
                            </button>

                            <p className="mt-3 flex items-start gap-1.5 text-xs text-gray-500">
                                <ShieldCheck className="mt-0.5 h-4 w-4 flex-shrink-0 text-ceylon-600" />
                                Deposit held in escrow. Contact details shared only after payment. You won't be charged until you confirm.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </SiteLayout>
    );
}

function Row({ label, value, bold, muted }: { label: string; value: string; bold?: boolean; muted?: boolean }) {
    return (
        <div className={`flex justify-between ${bold ? 'font-bold text-gray-900' : muted ? 'text-gray-500' : 'text-gray-700'}`}>
            <span>{label}</span><span>{value}</span>
        </div>
    );
}
