<?php

namespace App\Http\Controllers;

use App\Models\IdVerification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class VerificationSubmitController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'doc_type'    => 'required|in:nic,passport',
            'nic_front'   => 'required_if:doc_type,nic|nullable|image|max:8192',
            'nic_back'    => 'required_if:doc_type,nic|nullable|image|max:8192',
            'passport'    => 'required_if:doc_type,passport|nullable|image|max:8192',
            'selfie'      => 'required|image|max:8192',
        ]);

        $user = $request->user();
        $uid  = $user->id;

        $nicFront  = $request->file('nic_front')?->store("verifications/{$uid}", 'private');
        $nicBack   = $request->file('nic_back')?->store("verifications/{$uid}", 'private');
        $passport  = $request->file('passport')?->store("verifications/{$uid}", 'private');
        $selfie    = $request->file('selfie')->store("verifications/{$uid}", 'private');

        IdVerification::create([
            'user_id'        => $uid,
            'doc_type'       => $request->doc_type,
            'nic_front_path' => $nicFront,
            'nic_back_path'  => $nicBack,
            'passport_path'  => $passport,
            'selfie_path'    => $selfie,
            'status'         => 'pending',
            'sla_deadline'   => now()->addHours(24),
        ]);

        $user->update(['id_verification_status' => 'pending']);

        // Notify admin
        Mail::raw(
            "New ID verification submitted by {$user->name} ({$user->email}).\n\nReview at: https://rentceylon.com/admin/verifications",
            fn ($m) => $m->to('admin@rentceylon.com')->subject('New ID Verification — RentCeylon')
        );

        return redirect()->route('verification.id.show')
            ->with('success', 'Documents submitted! We\'ll review within 24 hours and email you the result.');
    }
}
