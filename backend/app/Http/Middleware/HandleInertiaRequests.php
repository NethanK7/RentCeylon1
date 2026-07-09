<?php

namespace App\Http\Middleware;

use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),

            'auth' => [
                'user' => $user ? [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->value,
                    'avatar_path' => $user->avatar_path,
                    'id_verification_status' => $user->id_verification_status->value,
                    'is_id_verified' => $user->isIdVerified(),
                ] : null,
            ],

            // Enabled category tree for the global nav / mega-menu (cached per request).
            'nav' => [
                'categories' => fn () => Category::query()
                    ->enabled()->topLevel()->orderBy('sort_order')
                    ->with(['children' => fn ($q) => $q->enabled()])
                    ->get(['id', 'name', 'slug', 'icon', 'kind'])
                    ->map(fn ($c) => [
                        'id' => $c->id,
                        'name' => $c->name,
                        'slug' => $c->slug,
                        'icon' => $c->icon,
                        'kind' => $c->kind,
                        'children' => $c->children->map(fn ($ch) => [
                            'name' => $ch->name, 'slug' => $ch->slug, 'icon' => $ch->icon,
                        ]),
                    ]),
            ],

            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }
}
