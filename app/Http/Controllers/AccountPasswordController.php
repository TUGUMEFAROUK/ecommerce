<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class AccountPasswordController extends Controller
{
    /**
     * Update the customer's password.
     *
     * The remember token is rotated as well so any "remember me" cookie
     * issued before the change stops working.
     */
    public function update(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->forceFill([
            'password' => $request->validated('password'),
            'remember_token' => Str::random(60),
        ])->save();

        return redirect()->route('account.show')->with('status', 'Password updated.');
    }
}
