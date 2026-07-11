import AdminLayout from '@/Layouts/AdminLayout';
import { router, useForm } from '@inertiajs/react';
import { useState } from 'react';

interface User {
    id: number; name: string; email: string; phone: string | null;
    role: string; city: string | null; id_verification_status: string;
    suspended: boolean; suspended_at: string | null; created_at: string;
    listings_count: number; bookings_as_renter_count: number; bookings_as_lister_count: number;
}

export default function UserShow({ user }: { user: User }) {
    const [showRoleModal, setShowRoleModal] = useState(false);
    const roleForm = useForm({ role: user.role });

    const suspend = () => { if (confirm(`Suspend ${user.name}?`)) router.post(`/admin/users/${user.id}/suspend`); };
    const unsuspend = () => router.post(`/admin/users/${user.id}/unsuspend`);
    const changeRole = () => { roleForm.post(`/admin/users/${user.id}/role`); setShowRoleModal(false); };

    return (
        <AdminLayout title={user.name}>
            <div className="max-w-2xl space-y-5">
                {/* Profile card */}
                <div className="rounded-2xl border border-gray-200 bg-white p-5">
                    <div className="flex items-start justify-between">
                        <div>
                            <h2 className="text-xl font-bold text-gray-900">{user.name}</h2>
                            <p className="text-gray-500">{user.email}</p>
                            {user.phone && <p className="text-gray-500">{user.phone}</p>}
                            {user.city && <p className="text-gray-400 text-sm mt-1">{user.city}</p>}
                        </div>
                        <div className="flex flex-col items-end gap-2">
                            <span className="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold capitalize">{user.role}</span>
                            {user.suspended && <span className="rounded-full bg-red-100 text-red-700 px-3 py-1 text-xs font-semibold">Suspended</span>}
                        </div>
                    </div>
                    <div className="mt-4 grid grid-cols-3 gap-4 border-t border-gray-100 pt-4">
                        <div className="text-center"><p className="text-2xl font-bold text-gray-900">{user.listings_count}</p><p className="text-xs text-gray-500">Listings</p></div>
                        <div className="text-center"><p className="text-2xl font-bold text-gray-900">{user.bookings_as_renter_count}</p><p className="text-xs text-gray-500">Bookings made</p></div>
                        <div className="text-center"><p className="text-2xl font-bold text-gray-900">{user.bookings_as_lister_count}</p><p className="text-xs text-gray-500">Bookings received</p></div>
                    </div>
                </div>

                {/* Info */}
                <div className="rounded-2xl border border-gray-200 bg-white p-5 space-y-2 text-sm">
                    <div className="flex justify-between"><span className="text-gray-500">Joined</span><span className="font-medium">{user.created_at}</span></div>
                    <div className="flex justify-between"><span className="text-gray-500">ID Verification</span><span className="font-medium capitalize">{user.id_verification_status}</span></div>
                    {user.suspended_at && <div className="flex justify-between"><span className="text-gray-500">Suspended at</span><span className="font-medium text-red-600">{user.suspended_at}</span></div>}
                </div>

                {/* Actions */}
                <div className="flex gap-3">
                    <button onClick={() => setShowRoleModal(true)} className="rounded-xl border border-gray-300 px-4 py-2 text-sm hover:bg-gray-50">Change role</button>
                    {user.suspended
                        ? <button onClick={unsuspend} className="rounded-xl bg-green-600 text-white px-4 py-2 text-sm hover:bg-green-700">Reinstate user</button>
                        : <button onClick={suspend} className="rounded-xl bg-red-600 text-white px-4 py-2 text-sm hover:bg-red-700">Suspend user</button>
                    }
                </div>
            </div>

            {/* Role modal */}
            {showRoleModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/40">
                    <div className="bg-white rounded-2xl p-6 w-80 shadow-xl">
                        <h3 className="font-bold text-gray-900 mb-4">Change role</h3>
                        <select className="w-full rounded-xl border border-gray-300 px-3 py-2 text-sm mb-4" value={roleForm.data.role} onChange={e => roleForm.setData('role', e.target.value)}>
                            <option value="renter">Renter</option>
                            <option value="lister">Lister</option>
                            <option value="manager">Manager</option>
                            <option value="admin">Admin</option>
                        </select>
                        <div className="flex gap-3">
                            <button onClick={() => setShowRoleModal(false)} className="flex-1 rounded-xl border border-gray-300 py-2 text-sm">Cancel</button>
                            <button onClick={changeRole} className="flex-1 rounded-xl bg-gray-900 text-white py-2 text-sm">Save</button>
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
