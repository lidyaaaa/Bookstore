<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(
        Request $request,
        Closure $next,
        string $role
    ): Response {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->role !== $role) {
            if ($request->isMethod('GET')) {
                return redirect()
                    ->route('dashboard')
                    ->withErrors([
                        'role' => 'Akses dibatasi: Halaman tersebut hanya dapat diakses oleh role '.strtoupper($role).'.',
                    ]);
            }

            abort(403, 'Anda tidak memiliki akses.');
        }

        return $next($request);
    }
}
