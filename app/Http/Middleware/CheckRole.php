<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!$request->user()) {
            abort(403, 'Unauthorized action.');
        }

        $userRole = $request->user()->role;

        $allowed = false;
        foreach ($roles as $role) {
            if ($userRole === $role) {
                $allowed = true;
                break;
            }
            // Kompatibilitas: Perlakukan role 'Orang Tua' dan 'Calon Siswa' setara
            if (($role === 'Orang Tua' || $role === 'Calon Siswa') && ($userRole === 'Orang Tua' || $userRole === 'Calon Siswa')) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            abort(403, 'Unauthorized action.');
        }

        return $next($request);
    }
}
