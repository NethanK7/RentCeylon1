import { Head } from '@inertiajs/react';
import { Globe2, UserCheck, ClipboardCheck, HandCoins, FileBarChart } from 'lucide-react';
import SiteLayout from '@/Layouts/SiteLayout';

export default function Landing() {
    return (
        <SiteLayout>
            <Head title="Property Management" />
            <section className="bg-gray-900 text-white">
                <div className="mx-auto max-w-5xl px-4 py-20 text-center sm:px-6 lg:px-8">
                    <div className="mb-3 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold"><Globe2 className="h-4 w-4" /> For Sri Lankans abroad</div>
                    <h1 className="font-display text-4xl font-extrabold sm:text-5xl">Your property. Managed from anywhere in the world.</h1>
                    <p className="mx-auto mt-4 max-w-2xl text-white/80">RentCeylon manages your property back home on your behalf — tenant vetting, condition inspections, rent collection and dispute handling. Management fee 8–12% of monthly rent.</p>
                </div>
            </section>

            <section className="mx-auto max-w-5xl px-4 py-14 sm:px-6 lg:px-8">
                <h2 className="mb-8 text-center font-display text-2xl font-bold text-gray-900">What's included</h2>
                <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    {[
                        [UserCheck, 'Tenant vetting', 'Background & reference checks on every tenant.'],
                        [ClipboardCheck, 'Inspections', 'Timestamped, geo-tagged condition reports.'],
                        [HandCoins, 'Rent collection', 'Monthly collection with paid / late tracking.'],
                        [FileBarChart, 'Monthly reporting', 'Downloadable statements for tax & legal use.'],
                    ].map(([I, t, b]: any, i) => (
                        <div key={i} className="rounded-2xl border border-gray-200 p-5">
                            <I className="h-6 w-6 text-ceylon-600" /><p className="mt-2 font-semibold text-gray-900">{t}</p><p className="text-sm text-gray-600">{b}</p>
                        </div>
                    ))}
                </div>

                <div className="mx-auto mt-10 max-w-lg rounded-2xl border border-gray-200 p-6 shadow-card">
                    <h3 className="text-lg font-bold text-gray-900">Enquire / join the waitlist</h3>
                    <form className="mt-4 space-y-3" onSubmit={(e) => e.preventDefault()}>
                        <input className="input" placeholder="Full name" />
                        <input className="input" placeholder="Email" type="email" />
                        <input className="input" placeholder="Which country do you live in now?" />
                        <input className="input" placeholder="City of your property (e.g. Colombo)" />
                        <textarea className="input" rows={3} placeholder="Tell us about your property" />
                        <button className="btn-primary w-full" type="submit">Request a callback</button>
                    </form>
                    <p className="mt-3 text-center text-xs text-gray-500">Licensed managers · Insured · Bonded</p>
                </div>
            </section>
        </SiteLayout>
    );
}
