<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        if (!$user) {
            abort(403, 'Non authentifié.');
        }

        // Vérifie si le rôle de l'utilisateur fait partie des rôles autorisés
        if (!in_array($user->role, $roles)) {
            abort(403, 'Accès refusé pour votre rôle.');
        }

        return $next($request);
    }
}
