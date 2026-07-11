import AdminLayout from '@/Layouts/AdminLayout';
import { router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { CheckCircle2, XCircle } from 'lucide-react';

interface Pending {
    id: number;
    user: { id: number; name: string; email: string };
    doc_type: string;
    submitted: string;
    sla_deadline: string | null;
}

function RejectModal({ id, onClose }: { id: number; onClose: () => void }) {
    const { data, setData, post, processing } = useForm({ reason: '' });
    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
            <div className="bg-white rounded-2xl p-6 w-96 shadow-xl">
                <h3 className="font-bold text-gray-900 mb-3">Rejection reason</h3>
                <textarea className="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm mb-4 h-24" placeholder="e.g. Photo was blurry, NIC number unclear…" value={data.reason} onChange={e => setData('reason', e.target.value)} />
                <div className="flex gap-3">
                    <button onClick={onClose} className="flex-1 rounded-xl border border-gray-300 py-2 text-sm">Cancel</button>
                    <button disabled={processing || !data.reason} onClick={() => post(`/admin/verifications/${id}/reject`, { onSuccess: onClose })} className="flex-1 rounded-xl bg-red-600 text-white py-2 text-sm disabled:opacity-50">Reject</button>
                </div>
            </div>
        </div>
    );
}

export default function VerificationsIndex({ pending }: { pending: Pending[] }) {
    const [rejectId, setRejectId] = useState<number | null>(null);

    const approve = (id: number) => { if (confirm('Approve this verification?')) router.post(`/admin/verifications/${id}/approve`); };

    return (
        <AdminLayout title="ID Verification Queue">
            {pending.length === 0 ? (
                <div className="rounded-2xl border border-gray-200 bg-white p-12 text-center">
                    <CheckCircle2 className="h-10 w-10 text-green-500 mx-auto mb-3" />
                    <p className="font-semibold text-gray-900">All clear!</p>
                    <p className="text-sm text-gray-500 mt-1">No pending ID verifications.</p>
                </div>
            ) : (
                <div className="space-y-3">
                    {pending.map((v) => (
                        <div key={v.id} className="rounded-2xl border border-gray-200 bg-white p-5 flex items-center justify-between gap-4">
                            <div>
                                <p className="font-semibold text-gray-900">{v.user.name}</p>
                                <p className="text-sm text-gray-500">{v.user.email}</p>
                                <div className="mt-1 flex gap-3 text-xs text-gray-400">
                                    <span>Doc: <span className="uppercase font-medium text-gray-600">{v.doc_type}</span></span>
                                    <span>Submitted: {v.submitted}</span>
                                    {v.sla_deadline && <span className="text-amber-600">SLA: {v.sla_deadline}</span>}
                                </div>
                            </div>
                            <div className="flex gap-2 flex-shrink-0">
                                <button onClick={() => setRejectId(v.id)} className="flex items-center gap-1.5 rounded-xl border border-red-300 px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    <XCircle className="h-4 w-4" /> Reject
                                </button>
                                <button onClick={() => approve(v.id)} className="flex items-center gap-1.5 rounded-xl bg-green-600 text-white px-4 py-2 text-sm hover:bg-green-700">
                                    <CheckCircle2 className="h-4 w-4" /> Approve
                                </button>
                            </div>
                        </div>
                    ))}
                </div>
            )}

            {rejectId && <RejectModal id={rejectId} onClose={() => setRejectId(null)} />}
        </AdminLayout>
    );
}
