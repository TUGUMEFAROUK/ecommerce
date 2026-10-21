<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Display the customer's account dashboard.
     */
    public function show(Request $request): View
    {
        return view('account.show', [
            'user' => $request->user(),
            'orders' => $request->user()->orders()->latest()->get(),
        ]);
    }

    /**
     * Update the customer's profile details.
     */
    public function update(UpdateAccountRequest $request): RedirectResponse
    {
        $request->user()->update($request->validated());

        return redirect()->route('account.show')->with('status', 'Profile updated.');
    }
}
