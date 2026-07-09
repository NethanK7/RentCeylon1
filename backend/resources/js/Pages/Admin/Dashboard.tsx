import DashboardShell, { StatCard } from '@/Components/site/DashboardShell';

export default function Dashboard() {
    return (
        <DashboardShell title="Admin panel" subtitle="Queues, SLAs and moderation. Every action is logged to an immutable audit trail.">
            <div className="grid gap-4 sm:grid-cols-4">
                <StatCard label="Dispute queue" value="0" hint="72hr SLA" />
                <StatCard label="ID verifications" value="0" hint="24hr SLA · alert 20hr" />
                <StatCard label="Deposit holds" value="0" hint="48hr SLA · alert 36hr" />
                <StatCard label="Flagged reviews/messages" value="0" hint="Moderation" />
            </div>
            <div className="mt-6 grid gap-4 lg:grid-cols-2">
                {['Dispute resolution', 'ID verification queue', 'Deposit release', 'Listing moderation', 'User management', 'Off-platform reports'].map((t) => (
                    <div key={t} className="rounded-2xl border border-gray-200 p-5">
                        <p className="font-semibold text-gray-900">{t}</p>
                        <p className="text-sm text-gray-500">No items in queue.</p>
                    </div>
                ))}
            </div>
        </DashboardShell>
    );
}
