<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($s) =>
                $s->where('name', 'like', "%{$request->q}%")
                  ->orWhere('email', 'like', "%{$request->q}%")))
            ->when($request->filled('role'), fn ($q) => $q->where('role', $request->role))
            ->latest()
            ->paginate(30)
            ->withQueryString()
            ->through(fn (User $u) => [
                'id'                      => $u->id,
                'name'                    => $u->name,
                'email'                   => $u->email,
                'role'                    => $u->role->value,
                'id_verification_status'  => $u->id_verification_status->value,
                'suspended'               => $u->isSuspended(),
                'created_at'              => $u->created_at->format('d M Y'),
            ]);

        return Inertia::render('Admin/Users/Index', [
            'users'   => $users,
            'filters' => $request->only(['q', 'role']),
        ]);
    }

    public function show(User $user)
    {
        $user->loadCount(['listings', 'bookingsAsRenter', 'bookingsAsLister']);

        return Inertia::render('Admin/Users/Show', [
            'user' => [
                'id'                      => $user->id,
                'name'                    => $user->name,
                'email'                   => $user->email,
                'phone'                   => $user->phone,
                'role'                    => $user->role->value,
                'city'                    => $user->city,
                'id_verification_status'  => $user->id_verification_status->value,
                'suspended'               => $user->isSuspended(),
                'suspended_at'            => $user->suspended_at?->format('d M Y H:i'),
                'created_at'              => $user->created_at->format('d M Y'),
                'listings_count'          => $user->listings_count,
                'bookings_as_renter_count'=> $user->bookings_as_renter_count,
                'bookings_as_lister_count'=> $user->bookings_as_lister_count,
            ],
        ]);
    }

    public function suspend(Request $request, User $user)
    {
        abort_if($user->isAdmin(), 403, 'Cannot suspend an admin.');
        $user->update(['suspended_at' => now()]);
        return back()->with('success', "{$user->name} has been suspended.");
    }

    public function unsuspend(User $user)
    {
        $user->update(['suspended_at' => null]);
        return back()->with('success', "{$user->name} has been reinstated.");
    }

    public function changeRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|in:renter,lister,admin,manager']);
        abort_if($user->id === $request->user()->id, 403, 'Cannot change your own role.');
        $user->update(['role' => $request->role]);
        return back()->with('success', "Role updated to {$request->role}.");
    }
}
