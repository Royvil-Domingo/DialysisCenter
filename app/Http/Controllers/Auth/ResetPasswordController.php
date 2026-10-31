<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    use ResetsPasswords;

    protected $redirectTo = '/dashboard';

    public function __construct()
    {
        $this->middleware('throttle:password-reset-submission')->only('reset');
    }

    public function reset(Request $request)
    {
        $email = strtolower((string) $request->input('email'));
        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if ($user && ! $user->is_active) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'This account is inactive and cannot reset its password.']);
        }

        return parent::reset($request);
    }
}
