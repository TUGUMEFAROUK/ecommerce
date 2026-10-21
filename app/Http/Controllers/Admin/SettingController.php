<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminSettingsRequest;
use App\Services\SettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Show the store settings form.
     */
    public function edit(SettingsService $settings): View
    {
        return view('admin.settings.edit', [
            'settings' => $settings->all(),
        ]);
    }

    /**
     * Validate and persist the store settings.
     *
     * Money is normalized to two decimal places and the currency code is
     * stored uppercased, so downstream consumers can rely on the format.
     */
    public function update(AdminSettingsRequest $request, SettingsService $settings): RedirectResponse
    {
        $validated = $request->validated();

        $settings->set([
            'store_name' => trim($validated['store_name']),
            'store_currency' => Str::upper($validated['store_currency']),
            'contact_email' => $validated['contact_email'] ?? '',
            'contact_phone' => $validated['contact_phone'] ?? '',
            'shipping_flat_fee' => number_format((float) $validated['shipping_flat_fee'], 2, '.', ''),
            'shipping_free_threshold' => number_format((float) $validated['shipping_free_threshold'], 2, '.', ''),
            'low_stock_threshold' => (string) (int) $validated['low_stock_threshold'],
        ]);

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Settings saved.');
    }
}
