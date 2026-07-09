import { PropsWithChildren } from 'react';
import { Head } from '@inertiajs/react';
import SiteLayout from '@/Layouts/SiteLayout';

export default function DashboardShell({ title, subtitle, children }: PropsWithChildren<{ title: string; subtitle?: string }>) {
    return (
        <SiteLayout>
            <Head title={title} />
            <div className="mx-auto max-w-6xl px-4 py-8 sm:px-6 lg:px-8">
                <h1 className="font-display text-2xl font-bold text-gray-900">{title}</h1>
                {subtitle && <p className="mt-1 text-gray-500">{subtitle}</p>}
                <div className="mt-6">{children}</div>
            </div>
        </SiteLayout>
    );
}

export function StatCard({ label, value, hint }: { label: string; value: string; hint?: string }) {
    return (
        <div className="rounded-2xl border border-gray-200 p-5">
            <p className="text-sm text-gray-500">{label}</p>
            <p className="mt-1 text-2xl font-bold text-gray-900">{value}</p>
            {hint && <p className="text-xs text-gray-400">{hint}</p>}
        </div>
    );
}
