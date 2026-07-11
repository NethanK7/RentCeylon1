<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IdVerification;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class VerificationController extends Controller
{
    public function index()
    {
        $pending = IdVerification::where('status', 'pending')
            ->with('user:id,name,email,created_at')
            ->latest()
            ->get()
            ->map(fn ($v) => [
                'id'         => $v->id,
                'user'       => ['id' => $v->user->id, 'name' => $v->user->name, 'email' => $v->user->email],
                'doc_type'   => $v->doc_type,
                'submitted'  => $v->created_at->format('d M Y H:i'),
                'sla_deadline' => $v->sla_deadline?->format('d M Y H:i'),
            ]);

        return Inertia::render('Admin/Verifications/Index', ['pending' => $pending]);
    }

    public function approve(Request $request, IdVerification $verification)
    {
        $verification->update([
            'status'      => 'approved',
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);

        $verification->user->update(['id_verification_status' => 'approved']);

        // Activate any listings that were held pending verification
        $verification->user->listings()
            ->where('status', 'pending_verification')
            ->update(['status' => 'active', 'published_at' => now()]);

        return back()->with('success', "{$verification->user->name} verified. Their listings are now live.");
    }

    public function reject(Request $request, IdVerification $verification)
    {
        $request->validate(['reason' => 'required|string|max:500']);

        $verification->update([
            'status'        => 'rejected',
            'reject_reason' => $request->reason,
            'reviewed_by'   => $request->user()->id,
            'reviewed_at'   => now(),
        ]);

        $verification->user->update(['id_verification_status' => 'rejected']);

        return back()->with('success', "Verification rejected.");
    }
}
