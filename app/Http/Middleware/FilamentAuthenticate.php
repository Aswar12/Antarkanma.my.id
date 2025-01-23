<?php

namespace App\Http\Middleware;

use Filament\Facades\Filament;
use Illuminate\Auth\Middleware\Authenticate as Middleware;

class FilamentAuthenticate extends Middleware
{
    protected function authenticate($request, array $guards): void
    {
        $guard = Filament::auth();

        \Log::info('Filament auth check:', [
            'is_authenticated' => $guard->check(),
            'request_path' => $request->path(),
            'request_method' => $request->method()
        ]);

        if (! $guard->check()) {
            \Log::warning('User not authenticated');
            $this->unauthenticated($request, $guards);
            return;
        }

        $this->auth->shouldUse(Filament::getAuthGuard());

        $user = $guard->user();
        
        \Log::info('User details:', [
            'id' => $user->id,
            'email' => $user->email,
            'roles' => $user->roles,
            'is_active' => $user->is_active ?? null
        ]);
        
        if ($user->roles !== 'ADMIN') {
            \Log::warning('User not authorized:', [
                'user_id' => $user->id,
                'roles' => $user->roles
            ]);
            abort(403, 'You are not authorized to access this area.');
        }

        \Log::info('Authentication successful');
    }

    protected function redirectTo($request): string
    {
        if ($request->expectsJson()) {
            abort(403, 'Unauthorized');
        }

        \Log::info('Redirecting to login page');
        return route('filament.admin.auth.login');
    }

    protected function unauthenticated($request, array $guards)
    {
        \Log::warning('Unauthenticated access attempt', [
            'path' => $request->path(),
            'method' => $request->method(),
            'guards' => $guards
        ]);

        parent::unauthenticated($request, $guards);
    }
}
