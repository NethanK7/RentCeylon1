import { Head } from '@inertiajs/react';
import { ShieldCheck, Upload, Clock } from 'lucide-react';
import SiteLayout from '@/Layouts/SiteLayout';

export default function Id({ status }: { status: string }) {
    return (
        <SiteLayout>
            <Head title="ID verification" />
            <div className="mx-auto max-w-2xl px-4 py-12 sm:px-6 lg:px-8">
                <div className="rounded-2xl border border-gray-200 p-6 shadow-card">
                    <div className="flex items-center gap-3">
                        <ShieldCheck className="h-8 w-8 text-ceylon-600" />
                        <div>
                            <h1 className="text-xl font-bold text-gray-900">Verify your identity</h1>
                            <p className="text-sm text-gray-500">Required before your listings go live. Reviewed within 24 hours.</p>
                        </div>
                    </div>

                    <div className="mt-4 inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-sm font-semibold text-amber-700">
                        <Clock className="h-4 w-4" /> Status: {status}
                    </div>

                    <form className="mt-6 space-y-4" onSubmit={(e) => e.preventDefault()}>
                        <UploadBox label="NIC front / Passport" />
                        <UploadBox label="NIC back (if NIC)" />
                        <UploadBox label="Selfie holding your ID" />
                        <button className="btn-primary w-full" type="submit">Submit for review</button>
                    </form>
                    <p className="mt-3 text-xs text-gray-500">Documents are encrypted at rest and accessed via signed URLs only. You'll get an email/SMS on approval or rejection.</p>
                </div>
            </div>
        </SiteLayout>
    );
}

function UploadBox({ label }: { label: string }) {
    return (
        <div>
            <p className="mb-1 text-sm font-semibold text-gray-900">{label}</p>
            <label className="flex cursor-pointer items-center justify-center gap-2 rounded-xl border-2 border-dashed border-gray-300 py-6 text-sm text-gray-500 hover:border-gray-400">
                <Upload className="h-5 w-5" /> Tap to upload
                <input type="file" accept="image/*" className="hidden" />
            </label>
        </div>
    );
}
