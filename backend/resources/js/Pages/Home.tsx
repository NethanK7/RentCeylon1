import { Head, Link } from '@inertiajs/react';
import { ShieldCheck, HandCoins, FileCheck2, Globe2, ArrowRight } from 'lucide-react';
import SiteLayout from '@/Layouts/SiteLayout';
import ListingCard from '@/Components/site/ListingCard';
import { ListingCardData } from '@/types/app';

interface CityRow { title: string; listings: ListingCardData[]; }

function ListingGrid({ listings }: { listings: ListingCardData[] }) {
    if (!listings.length) return null;
    return (
        <div className="grid grid-cols-2 gap-x-4 gap-y-8 sm:grid-cols-3 md:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6">
            {listings.map((l) => <ListingCard key={l.id} listing={l} />)}
        </div>
    );
}

function SectionHeading({ title, href }: { title: string; href?: string }) {
    return (
        <div className="mb-5 flex items-center justify-between">
            <h2 className="font-display text-xl font-bold text-gray-900 sm:text-2xl">{title}</h2>
            {href && (
                <Link href={href} className="flex items-center gap-1 text-sm font-semibold text-gray-600 hover:text-gray-900 hover:underline">
                    Show all <ArrowRight className="h-3.5 w-3.5" />
                </Link>
            )}
        </div>
    );
}

export default function Home({ featured, cityRows, stats }: {
    featured: ListingCardData[];
    cityRows: CityRow[];
    stats: { listings: number; cities: number };
}) {
    return (
        <SiteLayout showCategories>
            <Head title="Rent anything in Sri Lanka" />

            <div className="px-4 pt-6 sm:px-6 lg:px-8">
                {featured.length > 0 && (
                    <section className="mb-12">
                        <SectionHeading title="Featured near you" href="/browse" />
                        <ListingGrid listings={featured} />
                    </section>
                )}
                {cityRows.map((row) =>
                    row.listings.length > 0 ? (
                        <section key={row.title} className="mb-12">
                            <SectionHeading
                                title={row.title}
                                href={`/browse?city=${encodeURIComponent(row.title.replace('Popular rentals in ', ''))}`}
                            />
                            <ListingGrid listings={row.listings} />
                        </section>
                    ) : null
                )}
            </div>

            {/* How it works */}
            <section className="mt-6 bg-gray-50 py-14">
                <div className="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <h2 className="mb-8 text-center font-display text-2xl font-bold text-gray-900">How RentCeylon works</h2>
                    <div className="grid gap-6 md:grid-cols-3">
                        {[
                            ['List', 'Create a listing in minutes. Verified listers only.', FileCheck2],
                            ['Book', 'Pay securely — deposit held in escrow, contact revealed after payment.', HandCoins],
                            ['Return', 'Upload condition photos at pickup & return. Deposit released after confirmation.', ShieldCheck],
                        ].map(([title, body, I]: any, i) => (
                            <div key={i} className="card p-6">
                                <div className="flex h-11 w-11 items-center justify-center rounded-xl bg-gold-50 text-gold-600"><I className="h-6 w-6" /></div>
                                <p className="mt-4 text-lg font-bold text-gray-900">{i + 1}. {title}</p>
                                <p className="mt-1 text-sm text-gray-600">{body}</p>
                            </div>
                        ))}
                    </div>
                </div>
            </section>

            {/* Trust signals */}
            <section className="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8">
                <div className="grid gap-4 sm:grid-cols-3">
                    {[
                        [ShieldCheck, 'ID-verified listers', 'Every lister passes NIC/Passport verification before going live.'],
                        [HandCoins, 'Deposit protection', 'Security deposits held in escrow — released only after both parties confirm.'],
                        [FileCheck2, 'Rental agreement', 'A signed agreement is generated for every single booking.'],
                    ].map(([I, t, b]: any, i) => (
                        <div key={i} className="flex gap-3 rounded-2xl border border-gray-200 p-5">
                            <I className="h-6 w-6 flex-shrink-0 text-ceylon-600" />
                            <div><p className="font-semibold text-gray-900">{t}</p><p className="text-sm text-gray-600">{b}</p></div>
                        </div>
                    ))}
                </div>
                <p className="mt-6 text-center text-sm text-gray-500">{stats.listings}+ items across {stats.cities} cities in Sri Lanka</p>
            </section>

            {/* Property management teaser */}
            <section className="mx-auto max-w-7xl px-4 pb-16 sm:px-6 lg:px-8">
                <div className="flex flex-col items-start gap-6 overflow-hidden rounded-3xl bg-brand-900 p-8 text-white sm:flex-row sm:items-center sm:justify-between sm:p-12">
                    <div className="max-w-xl">
                        <div className="mb-3 inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold"><Globe2 className="h-4 w-4" /> For Sri Lankans abroad</div>
                        <h3 className="font-display text-2xl font-bold sm:text-3xl">Your property. Managed from anywhere in the world.</h3>
                        <p className="mt-2 text-white/80">Tenant vetting, inspections, rent collection and monthly reporting — handled by licensed local managers.</p>
                    </div>
                    <Link href="/property-management" className="btn bg-white text-brand-900 hover:bg-gray-100">Learn more</Link>
                </div>
            </section>
        </SiteLayout>
    );
}
