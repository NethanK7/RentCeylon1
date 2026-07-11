import { Head, useForm, usePage } from '@inertiajs/react';
import { ShieldCheck, Upload, Clock, CheckCircle2, X } from 'lucide-react';
import SiteLayout from '@/Layouts/SiteLayout';
import { PageProps } from '@/types/app';
import { useState } from 'react';

export default function Id({ status }: { status: string }) {
    const { flash } = usePage<PageProps>().props;
    const [docType, setDocType] = useState<'nic' | 'passport'>('nic');

    const { data, setData, post, processing, errors } = useForm<{
        doc_type: string;
        nic_front: File | null;
        nic_back: File | null;
        passport: File | null;
        selfie: File | null;
    }>({ doc_type: 'nic', nic_front: null, nic_back: null, passport: null, selfie: null });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post('/verify-id', { forceFormData: true });
    };

    if (status === 'approved') {
        return (
            <SiteLayout>
                <Head title="ID verified" />
                <div className="mx-auto max-w-2xl px-4 py-12">
                    <div className="rounded-2xl border border-green-200 bg-green-50 p-8 text-center">
                        <CheckCircle2 className="h-12 w-12 text-green-500 mx-auto mb-3" />
                        <h1 className="text-xl font-bold text-gray-900">Identity verified</h1>
                        <p className="text-gray-500 mt-1">Your listings are live and visible to renters.</p>
                    </div>
                </div>
            </SiteLayout>
        );
    }

    if (status === 'pending') {
        return (
            <SiteLayout>
                <Head title="Verification pending" />
                <div className="mx-auto max-w-2xl px-4 py-12">
                    <div className="rounded-2xl border border-amber-200 bg-amber-50 p-8 text-center">
                        <Clock className="h-12 w-12 text-amber-500 mx-auto mb-3" />
                        <h1 className="text-xl font-bold text-gray-900">Under review</h1>
                        <p className="text-gray-500 mt-1">We received your documents. You'll get an email within 24 hours.</p>
                    </div>
                </div>
            </SiteLayout>
        );
    }

    return (
        <SiteLayout>
            <Head title="Verify your identity" />
            <div className="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="rounded-2xl border border-gray-200 p-6 shadow-card">
                    <div className="flex items-center gap-3 mb-2">
                        <ShieldCheck className="h-8 w-8 text-ceylon-600" />
                        <div>
                            <h1 className="text-xl font-bold text-gray-900">Verify your identity</h1>
                            <p className="text-sm text-gray-500">Required before your listings go live. Reviewed within 24 hours.</p>
                        </div>
                    </div>

                    {status === 'rejected' && (
                        <div className="my-4 flex items-center gap-2 rounded-xl bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
                            <X className="h-4 w-4 flex-shrink-0" />
                            Your previous submission was rejected. Please resubmit with clearer photos.
                        </div>
                    )}

                    {flash?.success && (
                        <div className="my-4 rounded-xl bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">{flash.success}</div>
                    )}

                    <form onSubmit={submit} className="mt-4 space-y-5">
                        {/* Doc type selector */}
                        <div>
                            <p className="text-sm font-semibold text-gray-700 mb-2">Document type</p>
                            <div className="grid grid-cols-2 gap-3">
                                {(['nic', 'passport'] as const).map((t) => (
                                    <button key={t} type="button"
                                        onClick={() => { setDocType(t); setData('doc_type', t); }}
                                        className={`rounded-xl border p-3 text-sm font-medium transition ${docType === t ? 'border-ceylon-500 bg-ceylon-50 text-ceylon-700' : 'border-gray-300 text-gray-600 hover:border-gray-400'}`}>
                                        {t === 'nic' ? '🪪 NIC (National ID)' : '📘 Passport'}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {docType === 'nic' ? (
                            <>
                                <UploadBox label="NIC front" error={errors.nic_front} onChange={f => setData('nic_front', f)} />
                                <UploadBox label="NIC back" error={errors.nic_back} onChange={f => setData('nic_back', f)} />
                            </>
                        ) : (
                            <UploadBox label="Passport photo page" error={errors.passport} onChange={f => setData('passport', f)} />
                        )}

                        <UploadBox label="Selfie holding your ID" error={errors.selfie} onChange={f => setData('selfie', f)} hint="Hold your document next to your face, clearly visible." />

                        <button type="submit" disabled={processing} className="btn-primary w-full">
                            {processing ? 'Uploading…' : 'Submit for review'}
                        </button>
                    </form>

                    <p className="mt-3 text-xs text-gray-400">Documents are stored securely and only accessed by our verification team. You'll be emailed within 24 hours.</p>
                </div>
            </div>
        </SiteLayout>
    );
}

function UploadBox({ label, error, onChange, hint }: { label: string; error?: string; onChange: (f: File) => void; hint?: string }) {
    const [preview, setPreview] = useState<string | null>(null);

    const handle = (files: FileList | null) => {
        if (!files?.[0]) return;
        onChange(files[0]);
        setPreview(URL.createObjectURL(files[0]));
    };

    return (
        <div>
            <p className="mb-1 text-sm font-semibold text-gray-900">{label}</p>
            {hint && <p className="mb-2 text-xs text-gray-400">{hint}</p>}
            <label className={`flex cursor-pointer flex-col items-center justify-center gap-2 rounded-xl border-2 border-dashed py-5 text-sm transition ${preview ? 'border-green-400 bg-green-50' : 'border-gray-300 text-gray-500 hover:border-gray-400'}`}>
                {preview
                    ? <img src={preview} className="h-24 rounded-lg object-cover" alt="" />
                    : <><Upload className="h-5 w-5" /><span>Tap to upload</span></>
                }
                <input type="file" accept="image/*" className="hidden" onChange={e => handle(e.target.files)} />
            </label>
            {error && <p className="mt-1 text-xs text-red-600">{error}</p>}
        </div>
    );
}
