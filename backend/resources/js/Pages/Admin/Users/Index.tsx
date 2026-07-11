import AdminLayout from '@/Layouts/AdminLayout';
import { Link, router } from '@inertiajs/react';
import { useState } from 'react';
import { Paginated } from '@/types/app';

interface User {
    id: number; name: string; email: string; role: string;
    id_verification_status: string; suspended: boolean; created_at: string;
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

export default function UsersIndex({ users, filters }: { users: Paginated<User>; filters: Record<string, string> }) {
    const [q, setQ] = useState(filters.q ?? '');
    const [role, setRole] = useState(filters.role ?? '');

    const search = () => router.get('/admin/users', { q, role }, { preserveState: true, replace: true });

    return (
        <AdminLayout title="Users">
            <div className="flex gap-3 mb-5">
                <input
                    className="rounded-xl border border-gray-300 px-3 py-2 text-sm flex-1 max-w-xs"
                    placeholder="Search name or email…"
                    value={q}
                    onChange={e => setQ(e.target.value)}
                    onKeyDown={e => e.key === 'Enter' && search()}
                />
                <select className="rounded-xl border border-gray-300 px-3 py-2 text-sm" value={role} onChange={e => { setRole(e.target.value); router.get('/admin/users', { q, role: e.target.value }, { preserveState: true, replace: true }); }}>
                    <option value="">All roles</option>
                    <option value="renter">Renter</option>
                    <option value="lister">Lister</option>
                    <option value="admin">Admin</option>
                    <option value="manager">Manager</option>
                </select>
                <button onClick={search} className="rounded-xl bg-gray-900 px-4 py-2 text-sm text-white hover:bg-gray-700">Search</button>
            </div>

            <div className="rounded-2xl border border-gray-200 bg-white overflow-hidden">
                <table className="w-full text-sm">
                    <thead className="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">Name</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">Email</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">Role</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">ID Verified</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">Joined</th>
                            <th className="px-4 py-3 text-left font-medium text-gray-600">Status</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-100">
                        {users.data.map((u) => (
                            <tr key={u.id} className="hover:bg-gray-50">
                                <td className="px-4 py-3">
                                    <Link href={`/admin/users/${u.id}`} className="font-medium text-gray-900 hover:text-blue-600">{u.name}</Link>
                                </td>
                                <td className="px-4 py-3 text-gray-500">{u.email}</td>
                                <td className="px-4 py-3">
                                    <span className={`rounded-full px-2 py-0.5 text-xs font-medium capitalize ${roleBadge[u.role] ?? ''}`}>{u.role}</span>
                                </td>
                                <td className="px-4 py-3">
                                    <span className={`rounded-full px-2 py-0.5 text-xs font-medium capitalize ${verBadge[u.id_verification_status] ?? ''}`}>{u.id_verification_status}</span>
                                </td>
                                <td className="px-4 py-3 text-gray-500">{u.created_at}</td>
                                <td className="px-4 py-3">
                                    {u.suspended ? <span className="rounded-full bg-red-100 text-red-700 px-2 py-0.5 text-xs font-medium">Suspended</span> : <span className="text-gray-400 text-xs">Active</span>}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* Pagination */}
            {users.last_page > 1 && (
                <div className="mt-4 flex gap-1">
                    {users.links.map((l, i) => (
                        <Link key={i} href={l.url ?? '#'} className={`rounded-lg px-3 py-1.5 text-sm border ${l.active ? 'bg-gray-900 text-white border-gray-900' : 'border-gray-200 text-gray-600 hover:bg-gray-50'}`} dangerouslySetInnerHTML={{ __html: l.label }} />
                    ))}
                </div>
            )}
        </AdminLayout>
    );
}
