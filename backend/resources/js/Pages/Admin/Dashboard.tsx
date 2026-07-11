import AdminLayout from '@/Layouts/AdminLayout';
import { Link } from '@inertiajs/react';
import { Users, ListChecks, ShieldCheck, CalendarDays, AlertTriangle, Star } from 'lucide-react';

interface Stats {
    users: number;
    listings: number;
    bookings: number;
    pending_verifications: number;
    open_disputes: number;
    flagged_reviews: number;
    active_bookings: number;
}

interface RecentUser {
    id: number;
    name: string;
    email: string;
    role: string;
    id_verification_status: string;
    created_at: string;
}

function StatCard({ label, value, hint, href, color = 'blue' }: { label: string; value: number; hint?: string; href: string; color?: string }) {
    const colors: Record<string, string> = {
        blue: 'bg-blue-50 text-blue-700',
        green: 'bg-green-50 text-green-700',
        amber: 'bg-amber-50 text-amber-700',
        red: 'bg-red-50 text-red-700',
        purple: 'bg-purple-50 text-purple-700',
        rose: 'bg-rose-50 text-rose-700',
    };
    return (
        <Link href={href} className="rounded-2xl border border-gray-200 bg-white p-5 hover:shadow-md transition block">
            <p className="text-sm text-gray-500">{label}</p>
            <p className={`mt-1 inline-block rounded-lg px-2 py-0.5 text-2xl font-bold ${colors[color]}`}>{value}</p>
            {hint && <p className="mt-1 text-xs text-gray-400">{hint}</p>}
        </Link>
    );
}

const roleBadge: Record<string, string> = {
    admin: 'bg-rose-100 text-rose-700',
    lister: 'bg-blue-100 text-blue-700',
    renter: 'bg-gray-100 text-gray-700',
    manager: 'bg-purple-100 text-purple-700',
};

const verBadge: Record<string, string> = {
    approved: 'bg-green-100 text-green-700',
    pending: 'bg-amber-100 text-amber-700',
    rejected: 'bg-red-100 text-red-700',
    unsubmitted: 'bg-gray-100 text-gray-500',
};

export default function Dashboard({ stats, recent_users }: { stats: Stats; recent_users: RecentUser[] }) {
    return (
        <AdminLayout title="Dashboard">
            <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard label="Total Users" value={stats.users} href="/admin/users" color="blue" />
                <StatCard label="Total Listings" value={stats.listings} href="/admin/listings" color="green" />
                <StatCard label="Total Bookings" value={stats.bookings} hint={`${stats.active_bookings} active`} href="/admin/bookings" color="purple" />
                <StatCard label="Pending ID Verifications" value={stats.pending_verifications} hint="24hr SLA" href="/admin/verifications" color={stats.pending_verifications > 0 ? 'amber' : 'green'} />
                <StatCard label="Open Disputes" value={stats.open_disputes} hint="72hr SLA" href="/admin/bookings" color={stats.open_disputes > 0 ? 'red' : 'green'} />
                <StatCard label="Flagged Reviews" value={stats.flagged_reviews} href="/admin/reviews" color={stats.flagged_reviews > 0 ? 'rose' : 'green'} />
            </div>

            <div className="mt-8">
                <h2 className="font-semibold text-gray-900 mb-3">Recently joined users</h2>
                <div className="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                    <table className="w-full text-sm">
                        <thead className="bg-gray-50 border-b border-gray-200">
                            <tr>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Email</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Role</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">ID Status</th>
                                <th className="px-4 py-3 text-left font-medium text-gray-600">Joined</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {recent_users.map((u) => (
                                <tr key={u.id} className="hover:bg-gray-50">
                                    <td className="px-4 py-3">
                                        <Link href={`/admin/users/${u.id}`} className="font-medium text-gray-900 hover:text-blue-600">{u.name}</Link>
                                    </td>
                                    <td className="px-4 py-3 text-gray-500">{u.email}</td>
                                    <td className="px-4 py-3">
                                        <span className={`rounded-full px-2 py-0.5 text-xs font-medium capitalize ${roleBadge[u.role] ?? 'bg-gray-100 text-gray-700'}`}>{u.role}</span>
                                    </td>
                                    <td className="px-4 py-3">
                                        <span className={`rounded-full px-2 py-0.5 text-xs font-medium capitalize ${verBadge[u.id_verification_status] ?? ''}`}>{u.id_verification_status}</span>
                                    </td>
                                    <td className="px-4 py-3 text-gray-500">{u.created_at}</td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}
