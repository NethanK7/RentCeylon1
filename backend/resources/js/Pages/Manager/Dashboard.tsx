import { useState } from 'react';
import { router, useForm } from '@inertiajs/react';
import DashboardShell, { StatCard } from '@/Components/site/DashboardShell';
import {
    CheckCircle2,
    Clock,
    Plus,
} from 'lucide-react';
import { money } from '@/lib/format';

// ── Types ────────────────────────────────────────────────────────────────────

interface Property {
    id: number;
    title: string;
    address: string;
    city: string;
    district: string;
    monthly_rent: number;
    currency: string;
    tenant_name: string | null;
    tenant_phone: string | null;
    lease_start: string | null;
    lease_end: string | null;
    status: string;
    owner: { name: string };
    management_fee_rate: number | null;
}

interface Inspection {
    id: number;
    property_id: number;
    property_title: string;
    scheduled_at: string | null;
    completed_at: string | null;
    notes: string | null;
    status: string | null;
}

interface RentCollection {
    id: number;
    property_id: number;
    property_title: string;
    period: string;
    amount: number;
    management_fee: number | null;
    due_date: string | null;
    paid_at: string | null;
    status: string | null;
    notes: string | null;
}

interface Stats {
    total_properties: number;
    total_monthly_rent: number;
    this_month_collected: number;
    overdue_count: number;
    inspections_this_week: number;
}

interface Props {
    properties: Property[];
    inspections: Inspection[];
    rentCollections: RentCollection[];
    stats: Stats;
}

// ── Status badge helpers ─────────────────────────────────────────────────────

function PropertyStatusBadge({ status }: { status: string }) {
    const map: Record<string, string> = {
        active:      'bg-green-100 text-green-700',
        vacant:      'bg-gray-100 text-gray-600',
        maintenance: 'bg-amber-100 text-amber-700',
    };
    const cls = map[status] ?? 'bg-gray-100 text-gray-600';
    return (
        <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize ${cls}`}>
            {status}
        </span>
    );
}

function InspectionStatusBadge({ status, completedAt }: { status: string | null; completedAt: string | null }) {
    const done = !!completedAt || status === 'completed';
    return done ? (
        <span className="inline-flex items-center gap-1 text-xs font-medium text-green-600">
            <CheckCircle2 className="h-3.5 w-3.5" /> Done
        </span>
    ) : (
        <span className="inline-flex items-center gap-1 text-xs font-medium text-amber-600">
            <Clock className="h-3.5 w-3.5" /> Pending
        </span>
    );
}

// ── Tabs ─────────────────────────────────────────────────────────────────────

const TABS = ['Properties', 'Inspections', 'Rent Collection'] as const;
type Tab = (typeof TABS)[number];

// ── Main component ────────────────────────────────────────────────────────────

export default function ManagerDashboard({ properties, inspections, rentCollections, stats }: Props) {
    const [activeTab, setActiveTab] = useState<Tab>('Properties');
    const [showInspectionForm, setShowInspectionForm] = useState(false);

    const inspectionForm = useForm({
        property_id:  '',
        scheduled_at: '',
        notes:        '',
    });

    function submitInspection(e: React.FormEvent) {
        e.preventDefault();
        const pid = inspectionForm.data.property_id;
        if (!pid) return;
        router.post(
            `/manager/properties/${pid}/inspections`,
            {
                scheduled_at: inspectionForm.data.scheduled_at,
                notes:        inspectionForm.data.notes,
            },
            {
                onSuccess: () => {
                    inspectionForm.reset();
                    setShowInspectionForm(false);
                },
            },
        );
    }

    function markCollected(id: number) {
        router.post(`/manager/rent-collections/${id}/collect`, {});
    }

    return (
        <DashboardShell
            title="Manager Dashboard"
            subtitle="Manage your properties, inspections, and rent collections."
        >
            {/* ── Stat cards ── */}
            <div className="mb-8 grid grid-cols-2 gap-4 sm:grid-cols-4">
                <StatCard
                    label="Properties"
                    value={String(stats.total_properties)}
                    hint="under management"
                />
                <StatCard
                    label="Total Monthly Rent"
                    value={money(stats.total_monthly_rent)}
                />
                <StatCard
                    label="Collected This Month"
                    value={money(stats.this_month_collected)}
                />
                <StatCard
                    label="Overdue"
                    value={String(stats.overdue_count)}
                    hint="rent collections"
                />
            </div>

            {/* ── Tab bar ── */}
            <div className="mb-6 flex gap-1 rounded-xl border border-gray-200 bg-gray-50 p-1">
                {TABS.map((tab) => (
                    <button
                        key={tab}
                        onClick={() => setActiveTab(tab)}
                        className={`flex-1 rounded-lg px-3 py-2 text-sm font-semibold transition-colors ${
                            activeTab === tab
                                ? 'bg-white text-gray-900 shadow-sm'
                                : 'text-gray-500 hover:text-gray-700'
                        }`}
                    >
                        {tab}
                    </button>
                ))}
            </div>

            {/* ── Properties tab ── */}
            {activeTab === 'Properties' && (
                <div className="rounded-2xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50">
                                <tr>
                                    {['Property', 'Owner', 'Tenant', 'Monthly Rent', 'Lease End', 'Status'].map((h) => (
                                        <th
                                            key={h}
                                            className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                        >
                                            {h}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {properties.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="px-4 py-8 text-center text-gray-400">
                                            No properties assigned.
                                        </td>
                                    </tr>
                                )}
                                {properties.map((p) => (
                                    <tr key={p.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3">
                                            <p className="font-semibold text-gray-900">{p.title}</p>
                                            <p className="text-xs text-gray-500">{p.city}, {p.district}</p>
                                        </td>
                                        <td className="px-4 py-3 text-gray-700">{p.owner.name}</td>
                                        <td className="px-4 py-3">
                                            {p.tenant_name ? (
                                                <>
                                                    <p className="text-gray-900">{p.tenant_name}</p>
                                                    {p.tenant_phone && (
                                                        <p className="text-xs text-gray-500">{p.tenant_phone}</p>
                                                    )}
                                                </>
                                            ) : (
                                                <span className="text-gray-400">—</span>
                                            )}
                                        </td>
                                        <td className="px-4 py-3 text-gray-900">
                                            {money(p.monthly_rent, p.currency)}
                                        </td>
                                        <td className="px-4 py-3 text-gray-700">
                                            {p.lease_end ?? '—'}
                                        </td>
                                        <td className="px-4 py-3">
                                            <PropertyStatusBadge status={p.status} />
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}

            {/* ── Inspections tab ── */}
            {activeTab === 'Inspections' && (
                <div className="space-y-4">
                    {/* Schedule form toggle */}
                    <div className="flex justify-end">
                        <button
                            onClick={() => setShowInspectionForm((v) => !v)}
                            className="btn-primary flex items-center gap-2"
                        >
                            <Plus className="h-4 w-4" />
                            {showInspectionForm ? 'Cancel' : 'Schedule Inspection'}
                        </button>
                    </div>

                    {/* Collapsible form */}
                    {showInspectionForm && (
                        <form
                            onSubmit={submitInspection}
                            className="rounded-2xl border border-gray-200 p-5 space-y-4"
                        >
                            <h3 className="font-semibold text-gray-900">New Inspection</h3>

                            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                <div>
                                    <label className="mb-1 block text-xs font-semibold text-gray-500">
                                        Property <span className="text-red-500">*</span>
                                    </label>
                                    <select
                                        value={inspectionForm.data.property_id}
                                        onChange={(e) => inspectionForm.setData('property_id', e.target.value)}
                                        required
                                        className="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    >
                                        <option value="">Select property…</option>
                                        {properties.map((p) => (
                                            <option key={p.id} value={String(p.id)}>
                                                {p.title}
                                            </option>
                                        ))}
                                    </select>
                                    {inspectionForm.errors.property_id && (
                                        <p className="mt-1 text-xs text-red-500">{inspectionForm.errors.property_id}</p>
                                    )}
                                </div>

                                <div>
                                    <label className="mb-1 block text-xs font-semibold text-gray-500">
                                        Date &amp; Time <span className="text-red-500">*</span>
                                    </label>
                                    <input
                                        type="datetime-local"
                                        value={inspectionForm.data.scheduled_at}
                                        onChange={(e) => inspectionForm.setData('scheduled_at', e.target.value)}
                                        required
                                        className="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    />
                                    {inspectionForm.errors.scheduled_at && (
                                        <p className="mt-1 text-xs text-red-500">{inspectionForm.errors.scheduled_at}</p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <label className="mb-1 block text-xs font-semibold text-gray-500">Notes</label>
                                <textarea
                                    value={inspectionForm.data.notes}
                                    onChange={(e) => inspectionForm.setData('notes', e.target.value)}
                                    rows={3}
                                    className="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="Optional inspection notes…"
                                />
                                {inspectionForm.errors.notes && (
                                    <p className="mt-1 text-xs text-red-500">{inspectionForm.errors.notes}</p>
                                )}
                            </div>

                            <div className="flex justify-end gap-2">
                                <button
                                    type="button"
                                    onClick={() => setShowInspectionForm(false)}
                                    className="btn-outline"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={inspectionForm.processing}
                                    className="btn-primary"
                                >
                                    {inspectionForm.processing ? 'Saving…' : 'Schedule'}
                                </button>
                            </div>
                        </form>
                    )}

                    {/* Inspections list */}
                    <div className="rounded-2xl border border-gray-200 overflow-hidden">
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="bg-gray-50">
                                    <tr>
                                        {['Property', 'Scheduled', 'Completed', 'Notes', 'Status'].map((h) => (
                                            <th
                                                key={h}
                                                className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                            >
                                                {h}
                                            </th>
                                        ))}
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-gray-100">
                                    {inspections.length === 0 && (
                                        <tr>
                                            <td colSpan={5} className="px-4 py-8 text-center text-gray-400">
                                                No inspections yet.
                                            </td>
                                        </tr>
                                    )}
                                    {inspections.map((i) => (
                                        <tr key={i.id} className="hover:bg-gray-50">
                                            <td className="px-4 py-3 font-semibold text-gray-900">
                                                {i.property_title}
                                            </td>
                                            <td className="px-4 py-3 text-gray-700">
                                                {i.scheduled_at ?? '—'}
                                            </td>
                                            <td className="px-4 py-3 text-gray-700">
                                                {i.completed_at ?? '—'}
                                            </td>
                                            <td className="px-4 py-3 text-gray-500 max-w-xs truncate">
                                                {i.notes ?? '—'}
                                            </td>
                                            <td className="px-4 py-3">
                                                <InspectionStatusBadge
                                                    status={i.status}
                                                    completedAt={i.completed_at}
                                                />
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            )}

            {/* ── Rent Collection tab ── */}
            {activeTab === 'Rent Collection' && (
                <div className="rounded-2xl border border-gray-200 overflow-hidden">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm">
                            <thead className="bg-gray-50">
                                <tr>
                                    {['Property', 'Period', 'Amount', 'Due Date', 'Collected', 'Action'].map((h) => (
                                        <th
                                            key={h}
                                            className="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-gray-500"
                                        >
                                            {h}
                                        </th>
                                    ))}
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-gray-100">
                                {rentCollections.length === 0 && (
                                    <tr>
                                        <td colSpan={6} className="px-4 py-8 text-center text-gray-400">
                                            No rent collections for this month.
                                        </td>
                                    </tr>
                                )}
                                {rentCollections.map((r) => (
                                    <tr key={r.id} className="hover:bg-gray-50">
                                        <td className="px-4 py-3 font-semibold text-gray-900">
                                            {r.property_title}
                                        </td>
                                        <td className="px-4 py-3 text-gray-700">{r.period}</td>
                                        <td className="px-4 py-3 text-gray-900">
                                            {money(r.amount)}
                                        </td>
                                        <td className="px-4 py-3 text-gray-700">
                                            {r.due_date ?? '—'}
                                        </td>
                                        <td className="px-4 py-3">
                                            {r.paid_at ? (
                                                <span className="inline-flex items-center gap-1 text-xs font-medium text-green-600">
                                                    <CheckCircle2 className="h-3.5 w-3.5" />
                                                    {r.paid_at}
                                                </span>
                                            ) : (
                                                <span className="inline-flex items-center gap-1 text-xs font-medium text-gray-400">
                                                    <Clock className="h-3.5 w-3.5" />
                                                    Pending
                                                </span>
                                            )}
                                        </td>
                                        <td className="px-4 py-3">
                                            {!r.paid_at ? (
                                                <button
                                                    onClick={() => markCollected(r.id)}
                                                    className="btn-primary text-xs py-1 px-3"
                                                >
                                                    Mark Collected
                                                </button>
                                            ) : (
                                                <span className="text-xs text-gray-400">—</span>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
        </DashboardShell>
    );
}
