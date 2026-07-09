import { useRef } from 'react';
import { ChevronLeft, ChevronRight } from 'lucide-react';
import ListingCard from './ListingCard';
import { ListingCardData } from '@/types/app';

/** Airbnb-style horizontal carousel of listing cards ("Popular rentals in Colombo"). */
export default function ScrollRow({ title, listings, seeAllHref }: {
    title: string;
    listings: ListingCardData[];
    seeAllHref?: string;
}) {
    const trackRef = useRef<HTMLDivElement>(null);
    const scroll = (dir: number) => trackRef.current?.scrollBy({ left: dir * 640, behavior: 'smooth' });

    if (listings.length === 0) return null;

    return (
        <section className="group/row relative mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div className="mb-4 flex items-center justify-between">
                <h2 className="font-display text-xl font-bold text-gray-900 sm:text-2xl">{title}</h2>
                {seeAllHref && (
                    <a href={seeAllHref} className="text-sm font-semibold text-brand-700 hover:underline">Show all</a>
                )}
            </div>

            <div className="relative">
                <button
                    onClick={() => scroll(-1)}
                    aria-label="Scroll left"
                    className="absolute -left-3 top-[38%] z-10 hidden h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-gray-200 bg-white shadow-hover opacity-0 transition group-hover/row:opacity-100 lg:flex"
                >
                    <ChevronLeft className="h-4 w-4" />
                </button>

                <div ref={trackRef} className="flex gap-5 overflow-x-auto scroll-smooth pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                    {listings.map((l) => (
                        <div key={l.id} className="w-[220px] flex-shrink-0 sm:w-[240px]">
                            <ListingCard listing={l} />
                        </div>
                    ))}
                </div>

                <button
                    onClick={() => scroll(1)}
                    aria-label="Scroll right"
                    className="absolute -right-3 top-[38%] z-10 hidden h-9 w-9 -translate-y-1/2 items-center justify-center rounded-full border border-gray-200 bg-white shadow-hover opacity-0 transition group-hover/row:opacity-100 lg:flex"
                >
                    <ChevronRight className="h-4 w-4" />
                </button>
            </div>
        </section>
    );
}
