import { Star } from 'lucide-react';

export default function Stars({ rating, count, size = 14 }: { rating: number; count?: number; size?: number }) {
    return (
        <span className="inline-flex items-center gap-1 text-sm text-gray-700">
            <Star className="fill-gray-900 text-gray-900" style={{ width: size, height: size }} />
            <span className="font-semibold">{Number(rating).toFixed(1)}</span>
            {count !== undefined && <span className="text-gray-500">({count})</span>}
        </span>
    );
}
