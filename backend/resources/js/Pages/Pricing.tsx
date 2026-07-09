import { Head, Link } from '@inertiajs/react';
import { Check } from 'lucide-react';
import SiteLayout from '@/Layouts/SiteLayout';

const tiers = [
    { name: 'Basic', price: 'LKR 500', period: '/mo', features: ['Up to 3 active listings', '5 photos per listing', 'Standard categories', 'Deposit protection'], cta: 'Start listing', highlight: false },
    { name: 'Standard', price: 'LKR 2,000', period: '/mo', features: ['Up to 15 listings', '10 photos per listing', 'All categories', 'Badge eligibility', 'Priority support'], cta: 'Choose Standard', highlight: true },
    { name: 'Premium', price: 'LKR 5–10k', period: '/mo', features: ['Unlimited listings', '20 photos per listing', 'Sponsored badge credits', 'Featured placement', 'Analytics'], cta: 'Go Premium', highlight: false },
];

export default function Pricing() {
    return (
        <SiteLayout>
            <Head title="Pricing for listers" />
            <div className="mx-auto max-w-6xl px-4 py-14 sm:px-6 lg:px-8">
                <div className="text-center">
                    <h1 className="font-display text-3xl font-bold text-gray-900">Simple pricing for listers</h1>
                    <p className="mt-2 text-gray-600">Plus a transparent, tiered platform fee on each rental — 10% ≤ LKR 10k, 7% up to 50k, 5% above.</p>
                    <p className="mt-1 text-sm text-ceylon-700">Refer a lister → get 1 month free.</p>
                </div>

                <div className="mt-10 grid gap-6 md:grid-cols-3">
                    {tiers.map((t) => (
                        <div key={t.name} className={`rounded-2xl border p-6 ${t.highlight ? 'border-gold-500 shadow-hover ring-1 ring-gold-500' : 'border-gray-200 shadow-card'}`}>
                            {t.highlight && <p className="mb-2 inline-block rounded-full bg-gold-500 px-3 py-1 text-xs font-semibold text-white">Most popular</p>}
                            <h3 className="text-lg font-bold text-gray-900">{t.name}</h3>
                            <p className="mt-2"><span className="text-3xl font-extrabold text-gray-900">{t.price}</span><span className="text-gray-500">{t.period}</span></p>
                            <ul className="mt-5 space-y-2 text-sm text-gray-700">
                                {t.features.map((f) => <li key={f} className="flex gap-2"><Check className="h-5 w-5 text-ceylon-600" /> {f}</li>)}
                            </ul>
                            <Link href="/register?role=lister" className={`mt-6 w-full ${t.highlight ? 'btn-primary' : 'btn-outline'}`}>{t.cta}</Link>
                        </div>
                    ))}
                </div>

                <p className="mt-8 text-center text-sm text-gray-500">Every lister passes ID verification (24-hour SLA) before listings go live. Property tier — coming soon.</p>
            </div>
        </SiteLayout>
    );
}
