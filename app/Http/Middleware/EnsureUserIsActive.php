<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && ! $user->is_active) {
            return $this->logoutWithError($request, 'Your account is inactive. Please contact your administrator.');
        }

        // Patient accounts are only valid while linked to an ACTIVE patient record.
        if ($user && $user->isPatient()) {
            $patient = $user->patient;

            if (! $patient || $patient->status !== 'active') {
                return $this->logoutWithError(
                    $request,
                    'Your patient portal access is not available. Please contact the dialysis center.'
                );
            }
        }

        return $next($request);
    }

    private function logoutWithError(Request $request, string $message): Response
    {
        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->withErrors([
            'email' => $message,
        ]);
    }
}