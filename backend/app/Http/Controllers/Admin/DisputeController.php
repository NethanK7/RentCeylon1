<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dispute;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DisputeController extends Controller
{
    public function index()
    {
        $disputes = Dispute::with(['booking:id,reference,status', 'raisedBy:id,name,email'])
            ->orderByRaw("CASE status WHEN 'open' THEN 0 WHEN 'under_review' THEN 1 ELSE 2 END")
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->through(fn ($d) => [
                'id'           => $d->id,
                'booking'      => ['reference' => $d->booking->reference, 'status' => $d->booking->status],
                'raised_by'    => ['name' => $d->raisedBy->name, 'email' => $d->raisedBy->email],
                'type'         => $d->type,
                'description'  => $d->description,
                'status'       => $d->status,
                'sla_deadline' => $d->sla_deadline?->format('d M Y H:i'),
                'sla_breached' => $d->sla_breached,
                'created_at'   => $d->created_at->format('d M Y H:i'),
            ]);

        return Inertia::render('Admin/Disputes/Index', ['disputes' => $disputes]);
    }

    public function resolve(Request $request, Dispute $dispute)
    {
        $request->validate([
            'resolution_note'        => 'required|string|max:2000',
            'resolution_to_renter'   => 'required|numeric|min:0',
            'resolution_to_lister'   => 'required|numeric|min:0',
        ]);

        $dispute->update([
            'status'               => 'resolved',
            'resolution_note'      => $request->resolution_note,
            'resolution_to_renter' => $request->resolution_to_renter,
            'resolution_to_lister' => $request->resolution_to_lister,
            'resolved_by'          => $request->user()->id,
            'resolved_at'          => now(),
        ]);

        return back()->with('success', 'Dispute #' . $dispute->id . ' resolved.');
    }
}
