<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminSettingsRequest;
use App\Services\SettingsService;
use App\Services\StoreImageService;
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
    public function update(AdminSettingsRequest $request, SettingsService $settings, StoreImageService $images): RedirectResponse
    {
        $validated = $request->validated();

        $logo = $images->resolve(
            $settings->get('store_logo') ?: null,
            $request->file('store_logo_upload'),
            'store/logo',
            $request->boolean('store_logo_remove'),
        );

        $heroImage = $images->resolve(
            $settings->get('hero_image') ?: null,
            $request->file('hero_image_upload'),
            'store/hero',
            $request->boolean('hero_image_remove'),
        );

        $promoImage = $images->resolve(
            $settings->get('promo_image') ?: null,
            $request->file('promo_image_upload'),
            'store/promo',
            $request->boolean('promo_image_remove'),
        );

        $newsletterImage = $images->resolve(
            $settings->get('newsletter_image') ?: null,
            $request->file('newsletter_image_upload'),
            'store/newsletter',
            $request->boolean('newsletter_image_remove'),
        );

        $settings->set([
            'store_name' => trim($validated['store_name']),
            'store_tagline' => trim($validated['store_tagline'] ?? ''),
            'store_logo' => $logo ?? '',
            'brand_display' => $request->boolean('store_logo_remove')
                ? 'name'
                : (($validated['brand_display'] ?? $settings->brandDisplay()) === 'logo' ? 'logo' : 'name'),
            'store_logo_height' => (string) max(
                SettingsService::MIN_LOGO_HEIGHT,
                min(SettingsService::MAX_LOGO_HEIGHT, (int) ($validated['store_logo_height'] ?? $settings->logoHeight())),
            ),
            'store_currency' => Str::upper($validated['store_currency']),
            'contact_email' => $validated['contact_email'] ?? '',
            'contact_phone' => $validated['contact_phone'] ?? '',
            'shipping_flat_fee' => number_format((float) $validated['shipping_flat_fee'], 2, '.', ''),
            'shipping_free_threshold' => number_format((float) $validated['shipping_free_threshold'], 2, '.', ''),
            'delivery_estimate' => trim($validated['delivery_estimate'] ?? ''),
            'low_stock_threshold' => (string) (int) $validated['low_stock_threshold'],
            'returns_window_days' => (string) (int) ($validated['returns_window_days'] ?? $settings->returnsWindowDays()),
            'announcement_enabled' => $request->boolean('announcement_enabled') ? '1' : '0',
            'announcement_text' => trim($validated['announcement_text'] ?? ''),
            'announcement_link' => trim($validated['announcement_link'] ?? ''),
            'hero_eyebrow' => trim($validated['hero_eyebrow'] ?? ''),
            'hero_heading' => trim($validated['hero_heading'] ?? ''),
            'hero_body' => trim($validated['hero_body'] ?? ''),
            'hero_cta_label' => trim($validated['hero_cta_label'] ?? ''),
            'hero_cta_url' => trim($validated['hero_cta_url'] ?? ''),
            'hero_image' => $heroImage ?? '',
            'promo_eyebrow' => trim($validated['promo_eyebrow'] ?? ''),
            'promo_heading' => trim($validated['promo_heading'] ?? ''),
            'promo_body' => trim($validated['promo_body'] ?? ''),
            'promo_cta_label' => trim($validated['promo_cta_label'] ?? ''),
            'promo_cta_url' => trim($validated['promo_cta_url'] ?? ''),
            'promo_image' => $promoImage ?? '',
            'newsletter_heading' => trim($validated['newsletter_heading'] ?? ''),
            'newsletter_body' => trim($validated['newsletter_body'] ?? ''),
            'newsletter_image' => $newsletterImage ?? '',
            'social_facebook' => trim($validated['social_facebook'] ?? ''),
            'social_instagram' => trim($validated['social_instagram'] ?? ''),
            'social_x' => trim($validated['social_x'] ?? ''),
            'social_pinterest' => trim($validated['social_pinterest'] ?? ''),
        ]);

        return redirect()
            ->route('admin.settings.edit')
            ->with('status', 'Settings saved.');
    }
}
