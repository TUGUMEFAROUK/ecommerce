<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class NewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        return view('auth.reset-password', [
            'token' => $request->route('token'),
            'email' => $request->email,
        ]);
    }

    /**
     * Handle an incoming password reset request.
     */
    public function store(ResetPasswordRequest $request): RedirectResponse
    {
        $resetUser = null;

        $status = Password::broker()->reset(
            $request->safe()->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) use (&$resetUser) {
                $user->forceFill([
                    'password' => $password,
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                $resetUser = $user;
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw ValidationException::withMessages([
                'email' => [__($status)],
            ]);
        }

        Auth::guard('web')->login($resetUser);

        $request->session()->regenerate();

        // A password reset usually means the old password may be
        // compromised: end every other authenticated session for this
        // account, keeping only the session the user just established.
        DB::table('sessions')
            ->where('user_id', $resetUser->getKey())
            ->where('id', '!=', $request->session()->getId())
            ->delete();

        return redirect()->route('account.show')->with('status', __($status));
    }
}
