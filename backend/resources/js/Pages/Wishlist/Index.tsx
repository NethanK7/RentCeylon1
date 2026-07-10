import { Head, Link } from '@inertiajs/react';
import { Heart } from 'lucide-react';
import SiteLayout from '@/Layouts/SiteLayout';
import ListingCard from '@/Components/site/ListingCard';
import { ListingCardData } from '@/types/app';

export default function Index({ listings }: { listings: ListingCardData[] }) {
    return (
        <SiteLayout>
            <Head title="Wishlists" />
            <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
                <h1 className="font-display text-2xl font-bold text-gray-900">Wishlists</h1>
                <p className="mt-1 text-gray-500">Items you've saved for later.</p>

                {listings.length === 0 ? (
                    <div className="mt-8 rounded-2xl border border-dashed border-gray-300 py-16 text-center text-gray-500">
                        <Heart className="mx-auto h-10 w-10 text-gray-300" />
                        <p className="mt-3">Nothing saved yet. Tap the heart on any listing to save it here.</p>
                        <Link href="/browse" className="btn-primary mt-4 inline-flex">Browse rentals</Link>
                    </div>
                ) : (
                    <div className="mt-6 grid grid-cols-1 gap-x-5 gap-y-8 sm:grid-cols-2 xl:grid-cols-3">
                        {listings.map((l) => <ListingCard key={l.id} listing={l} />)}
                    </div>
                )}
            </div>
        </SiteLayout>
    );
}
