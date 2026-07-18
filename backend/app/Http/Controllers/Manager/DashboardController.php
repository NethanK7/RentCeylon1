<?php

namespace App\Http\Controllers\Manager;

use App\Http\Controllers\Controller;
use App\Models\Inspection;
use App\Models\ManagedProperty;
use App\Models\RentCollection;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $managerId = auth()->id();

        $properties = ManagedProperty::where('manager_id', $managerId)
            ->with('owner:id,name')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($p) => [
                'id'                  => $p->id,
                'title'               => $p->title,
                'address'             => $p->address,
                'city'                => $p->city,
                'district'            => $p->district,
                'monthly_rent'        => $p->monthly_rent,
                'currency'            => $p->currency ?? 'LKR',
                'tenant_name'         => $p->tenant_name,
                'tenant_phone'        => $p->tenant_phone,
                'lease_start'         => $p->lease_start?->format('d M Y'),
                'lease_end'           => $p->lease_end?->format('d M Y'),
                'status'              => $p->status,
                'owner'               => ['name' => $p->owner->name ?? '—'],
                'management_fee_rate' => $p->management_fee_rate,
            ]);

        $propertyIds = ManagedProperty::where('manager_id', $managerId)->pluck('id');

        $inspections = Inspection::whereIn('property_id', $propertyIds)
            ->with('property:id,title')
            ->orderBy('scheduled_at', 'asc')
            ->get()
            ->map(fn ($i) => [
                'id'             => $i->id,
                'property_id'    => $i->property_id,
                'property_title' => $i->property->title ?? '—',
                'scheduled_at'   => $i->scheduled_at?->format('d M Y H:i'),
                'completed_at'   => $i->completed_at?->format('d M Y'),
                'notes'          => $i->notes,
                'status'         => $i->status,
            ]);

        $currentMonth = now()->format('Y-m');
        $rentCollections = RentCollection::whereIn('property_id', $propertyIds)
            ->where('period', 'like', $currentMonth . '%')
            ->with('property:id,title')
            ->get()
            ->map(fn ($r) => [
                'id'             => $r->id,
                'property_id'    => $r->property_id,
                'property_title' => $r->property->title ?? '—',
                'period'         => $r->period,
                'amount'         => $r->amount,
                'management_fee' => $r->management_fee,
                'due_date'       => $r->due_date?->format('d M Y'),
                'paid_at'        => $r->paid_at?->format('d M Y'),
                'status'         => $r->status,
                'notes'          => $r->notes ?? null,
            ]);

        $stats = [
            'total_properties'      => $properties->count(),
            'total_monthly_rent'    => $properties->sum('monthly_rent'),
            'this_month_collected'  => $rentCollections->whereNotNull('paid_at')->sum('amount'),
            'overdue_count'         => $rentCollections->where('status', 'overdue')->count(),
            'inspections_this_week' => Inspection::whereIn('property_id', $propertyIds)
                ->whereBetween('scheduled_at', [now()->startOfWeek(), now()->endOfWeek()])
                ->count(),
        ];

        return Inertia::render('Manager/Dashboard', compact('properties', 'inspections', 'rentCollections', 'stats'));
    }

    public function scheduleInspection(Request $request, ManagedProperty $property)
    {
        $request->validate([
            'scheduled_at' => 'required|date',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $property->inspections()->create([
            'manager_id'   => auth()->id(),
            'scheduled_at' => $request->scheduled_at,
            'notes'        => $request->notes,
            'status'       => 'scheduled',
        ]);

        return back()->with('success', 'Inspection scheduled for ' . $property->title . '.');
    }

    public function markCollected(Request $request, RentCollection $collection)
    {
        $collection->update([
            'paid_at' => now(),
            'status'  => 'paid',
        ]);

        return back()->with('success', 'Rent marked as collected.');
    }
}
