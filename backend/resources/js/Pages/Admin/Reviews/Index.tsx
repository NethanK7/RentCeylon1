import AdminLayout from '@/Layouts/AdminLayout';
import { router } from '@inertiajs/react';
import { Eye, EyeOff, Star } from 'lucide-react';

interface FlaggedReview {
    id: number; rating: number; body: string; direction: string;
    is_visible: boolean; author: string; subject: string;
    reference: string; flag_count: number; submitted: string | null;
}

export default function ReviewsIndex({ flagged }: { flagged: FlaggedReview[] }) {
    const hide = (id: number) => router.post(`/admin/reviews/${id}/hide`);
    const keep = (id: number) => router.post(`/admin/reviews/${id}/keep`);

    return (
        <AdminLayout title="Flagged Reviews">
            {flagged.length === 0 ? (
                <div className="rounded-2xl border border-gray-200 bg-white p-12 text-center">
                    <Star className="h-10 w-10 text-green-500 mx-auto mb-3" />
                    <p className="font-semibold text-gray-900">No flagged reviews</p>
                    <p className="text-sm text-gray-500 mt-1">All reviews are clean.</p>
                </div>
            ) : (
                <div className="space-y-3">
                    {flagged.map((r) => (
                        <div key={r.id} className="rounded-2xl border border-gray-200 bg-white p-5">
                            <div className="flex items-start justify-between gap-4">
                                <div className="flex-1">
                                    <div className="flex items-center gap-3 mb-2">
                                        <div className="flex gap-0.5">
                                            {Array.from({ length: 5 }).map((_, i) => (
                                                <Star key={i} className={`h-4 w-4 ${i < r.rating ? 'text-amber-400 fill-amber-400' : 'text-gray-200 fill-gray-200'}`} />
                                            ))}
                                        </div>
                                        <span className="text-sm font-medium text-gray-900">{r.author}</span>
                                        <span className="text-xs text-gray-400">→ {r.subject}</span>
                                        <span className="rounded-full bg-red-100 text-red-700 px-2 py-0.5 text-xs">{r.flag_count} flag{r.flag_count !== 1 ? 's' : ''}</span>
                                        {!r.is_visible && <span className="rounded-full bg-gray-100 text-gray-500 px-2 py-0.5 text-xs">Hidden</span>}
                                    </div>
                                    <p className="text-sm text-gray-700">{r.body}</p>
                                    <p className="mt-1 text-xs text-gray-400">Booking {r.reference} · {r.submitted}</p>
                                </div>
                                <div className="flex gap-2 flex-shrink-0">
                                    <button onClick={() => keep(r.id)} className="flex items-center gap-1.5 rounded-xl border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50">
                                        <Eye className="h-4 w-4" /> Keep
                                    </button>
                                    <button onClick={() => hide(r.id)} className="flex items-center gap-1.5 rounded-xl bg-red-600 text-white px-3 py-1.5 text-sm hover:bg-red-700">
                                        <EyeOff className="h-4 w-4" /> Hide
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>
            )}
        </AdminLayout>
    );
}
