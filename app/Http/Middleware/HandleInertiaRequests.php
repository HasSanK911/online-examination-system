<?php

namespace App\Http\Middleware;

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
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user() ? array_merge(
                    $request->user()->only('id', 'name', 'email', 'avatar', 'role_type', 'is_active'),
                    [
                        'avatar_url' => $request->user()->avatar_url,
                        'roles'      => $request->user()->roles->map(fn($r) => ['name' => $r->name]),
                    ]
                ) : null,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error'   => $request->session()->get('error'),
            ],
        ];
    }
}
