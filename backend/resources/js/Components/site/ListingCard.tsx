import { Link } from '@inertiajs/react';
import { useState } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import { ListingCardData } from '@/types/app';
import { money } from '@/lib/format';
import Stars from './Stars';
import { EarnedBadge, PromotedBadge } from './Badges';

export default function ListingCard({ listing }: { listing: ListingCardData }) {
    const photos = listing.photos?.length ? listing.photos : listing.photo ? [listing.photo] : [];
    const [idx, setIdx] = useState(0);

    const go = (e: React.MouseEvent, dir: number) => {
        e.preventDefault();
        e.stopPropagation();
        setIdx((i) => (i + dir + photos.length) % photos.length);
    };

    return (
        <Link href={`/listings/${listing.slug}`} className="group block">
            <div className="relative aspect-[4/3] overflow-hidden rounded-2xl bg-gray-100">
                {photos.length > 0 ? (
                    <img
                        src={photos[idx]}
                        alt={listing.title}
                        loading="lazy"
                        className="h-full w-full object-cover transition group-hover:scale-105"
                    />
                ) : (
                    <div className="flex h-full items-center justify-center text-gray-400">No photo</div>
                )}

                {/* Promoted badge floats top-left; earned float differently (top-right) */}
                {listing.promotedBadges?.[0] && (
                    <div className="absolute left-3 top-3">
                        <PromotedBadge badge={listing.promotedBadges[0]} />
                    </div>
                )}
                {listing.earnedBadges?.[0] && (
                    <div className="absolute right-3 top-3">
                        <EarnedBadge badge={listing.earnedBadges[0]} />
                    </div>
                )}

                {photos.length > 1 && (
                    <>
                        <button onClick={(e) => go(e, -1)} aria-label="Previous"
                            className="absolute left-2 top-1/2 hidden -translate-y-1/2 rounded-full bg-white/90 p-1.5 shadow hover:bg-white group-hover:block">
                            <ChevronLeft className="h-4 w-4" />
                        </button>
                        <button onClick={(e) => go(e, 1)} aria-label="Next"
                            className="absolute right-2 top-1/2 hidden -translate-y-1/2 rounded-full bg-white/90 p-1.5 shadow hover:bg-white group-hover:block">
                            <ChevronRight className="h-4 w-4" />
                        </button>
                        <div className="absolute bottom-2 left-0 right-0 flex justify-center gap-1">
                            {photos.map((_, i) => (
                                <span key={i} className={`h-1.5 w-1.5 rounded-full ${i === idx ? 'bg-white' : 'bg-white/50'}`} />
                            ))}
                        </div>
                    </>
                )}
            </div>

            <div className="mt-2.5">
                <div className="flex items-start justify-between gap-2">
                    <h3 className="line-clamp-1 font-semibold text-gray-900">{listing.title}</h3>
                    {listing.rating_count > 0 && <Stars rating={listing.rating_avg} />}
                </div>
                <p className="text-sm text-gray-500">{listing.city} · {listing.category}</p>
                <p className="mt-1 text-gray-900">
                    <span className="font-semibold">{money(listing.daily_rate, listing.currency)}</span>
                    <span className="text-gray-500"> / day</span>
                </p>
            </div>
        </Link>
    );
}
