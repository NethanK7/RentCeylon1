import DashboardShell, { StatCard } from '@/Components/site/DashboardShell';

export default function Dashboard() {
    return (
        <DashboardShell title="Property Manager portal" subtitle="Assigned properties, inspections and rent collection.">
            <div className="grid gap-4 sm:grid-cols-3">
                <StatCard label="Assigned properties" value="0" />
                <StatCard label="Inspections due" value="0" />
                <StatCard label="Rent outstanding" value="LKR 0" />
            </div>
            <div className="mt-6 rounded-2xl border border-dashed border-gray-300 p-10 text-center text-gray-500">
                No properties assigned yet. RentCeylon ops will assign properties to you.
            </div>
        </DashboardShell>
    );
}
