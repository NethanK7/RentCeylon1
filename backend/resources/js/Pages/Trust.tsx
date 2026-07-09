import { Head } from '@inertiajs/react';
import { ShieldCheck, Clock, HandCoins, FileText, MessageSquareWarning, BadgeCheck } from 'lucide-react';
import SiteLayout from '@/Layouts/SiteLayout';

const items = [
    [ShieldCheck, 'ID verification', 'Listers upload NIC or passport + a selfie. Reviewed within a 24-hour SLA. Listings stay hidden until approved.'],
    [HandCoins, 'Security deposit & escrow', 'Deposits are held separately from lister earnings and released only by explicit confirmation or admin resolution — never automatically.'],
    [Clock, 'Cancellation policy', '7+ days before: full refund minus fee. 3–6 days: 50% refund. Under 3 days: no rental refund. Deposit always returned unless no-show.'],
    [FileText, 'Rental agreement', 'A signed agreement is generated and stored for every booking, downloadable by both parties.'],
    [MessageSquareWarning, 'Off-platform policy', 'Phone numbers are revealed only after payment. Messages are monitored and retained; dealing off-platform can suspend your account.'],
    [BadgeCheck, 'Badges: earned vs paid', 'Earned badges (Top Rated, Verified Item, Fast Responder) are always shown separately from paid Sponsored/Featured placements.'],
];

export default function Trust() {
    return (
        <SiteLayout>
            <Head title="Trust & safety" />
            <div className="mx-auto max-w-4xl px-4 py-14 sm:px-6 lg:px-8">
                <h1 className="font-display text-3xl font-bold text-gray-900">Trust & safety at RentCeylon</h1>
                <p className="mt-2 text-gray-600">Everything that keeps renting on RentCeylon safe for both sides.</p>
                <div className="mt-8 grid gap-4 sm:grid-cols-2">
                    {items.map(([I, t, b]: any, i) => (
                        <div key={i} className="rounded-2xl border border-gray-200 p-5">
                            <I className="h-6 w-6 text-ceylon-600" />
                            <p className="mt-2 font-semibold text-gray-900">{t}</p>
                            <p className="mt-1 text-sm text-gray-600">{b}</p>
                        </div>
                    ))}
                </div>
                <div className="mt-8 rounded-2xl bg-gray-900 p-6 text-white">
                    <p className="font-semibold">Admin SLA commitments</p>
                    <p className="mt-1 text-sm text-white/80">ID verification: 24 hours · Disputes: 72 hours (deposit defaults to renter on breach) · Deposit holds: 48 hours. Every breach is logged — never dropped silently.</p>
                </div>
            </div>
        </SiteLayout>
    );
}
