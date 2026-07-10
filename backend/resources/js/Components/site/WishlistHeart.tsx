import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Heart } from 'lucide-react';
import { PageProps } from '@/types/app';

/**
 * Airbnb-style save/heart toggle. Optimistic: flips instantly on tap, then
 * fires the request in the background (reverts on failure). Guests are sent
 * to log in — wishlists are per-account.
 */
export default function WishlistHeart({ listingId, className = '' }: { listingId: number; className?: string }) {
    const { auth, wishlist } = usePage<PageProps>().props;
    const [saved, setSaved] = useState(() => wishlist.ids.includes(listingId));
    const [busy, setBusy] = useState(false);

    const toggle = (e: React.MouseEvent) => {
        e.preventDefault();
        e.stopPropagation();

        if (!auth.user) {
            router.visit('/login');
            return;
        }
        if (busy) return;

        const next = !saved;
        setSaved(next);
        setBusy(true);

        router.post(`/listings/${listingId}/wishlist`, {}, {
            preserveScroll: true,
            preserveState: true,
            onError: () => setSaved(!next),
            onFinish: () => setBusy(false),
        });
    };

    return (
        <button
            onClick={toggle}
            aria-label={saved ? 'Remove from wishlist' : 'Save to wishlist'}
            aria-pressed={saved}
            className={`group/heart flex h-8 w-8 items-center justify-center rounded-full transition active:scale-90 ${className}`}
        >
            <Heart
                className={`h-5 w-5 drop-shadow transition ${saved ? 'fill-brand-600 text-brand-600' : 'fill-black/25 text-white group-hover/heart:fill-black/40'}`}
                strokeWidth={2}
            />
        </button>
    );
}
