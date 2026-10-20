<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails;

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $user = User::whereRaw('LOWER(email) = ?', [strtolower((string) $request->email)])->first();

        if ($user && ! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['This account is inactive and cannot reset its password.'],
            ]);
        }

        $response = $this->broker()->sendResetLink($this->credentials($request));

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }
}
