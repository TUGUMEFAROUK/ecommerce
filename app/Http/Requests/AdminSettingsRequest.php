<?php

namespace App\Http\Requests;

use App\Rules\ImageDimensions;
use App\Support\Currency;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class AdminSettingsRequest extends FormRequest
{
    /**
     * Normalize the currency code to uppercase before validation, so the
     * allowlist check and the stored value behave identically for input
     * like "eur".
     */
    protected function prepareForValidation(): void
    {
        $currency = $this->input('store_currency');

        if (is_string($currency)) {
            $this->merge(['store_currency' => Str::upper($currency)]);
        }
    }

    /**
     * Determine if the user is authorized to make this request.
     *
     * Admin-only form request: the route middleware enforces this too, but
     * the request itself never trusts a missing middleware.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'max:100'],
            'store_tagline' => ['nullable', 'string', 'max:500'],
            'store_logo_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:2048', new ImageDimensions(minWidth: 32, minHeight: 32, maxWidth: 2400, maxHeight: 2400)],
            'store_logo_remove' => ['nullable', 'boolean'],
            'brand_display' => ['nullable', 'string', Rule::in(['logo', 'name'])],
            'store_logo_height' => ['nullable', 'integer', 'min:16', 'max:64'],
            'store_currency' => ['required', 'string', 'size:3', Rule::in(Currency::codes())],
            'contact_email' => ['nullable', 'string', 'email', 'max:255'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'shipping_flat_fee' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'shipping_free_threshold' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'delivery_estimate' => ['nullable', 'string', 'max:100'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:1000000'],
            'returns_window_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'announcement_enabled' => ['nullable', 'boolean'],
            'announcement_text' => ['nullable', 'string', 'max:255'],
            'announcement_link' => ['nullable', 'string', 'max:255'],
            'hero_eyebrow' => ['nullable', 'string', 'max:100'],
            'hero_heading' => ['nullable', 'string', 'max:255'],
            'hero_body' => ['nullable', 'string', 'max:1000'],
            'hero_cta_label' => ['nullable', 'string', 'max:100'],
            'hero_cta_url' => ['nullable', 'string', 'max:255'],
            'hero_image_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'hero_image_remove' => ['nullable', 'boolean'],
            'promo_eyebrow' => ['nullable', 'string', 'max:100'],
            'promo_heading' => ['nullable', 'string', 'max:255'],
            'promo_body' => ['nullable', 'string', 'max:1000'],
            'promo_cta_label' => ['nullable', 'string', 'max:100'],
            'promo_cta_url' => ['nullable', 'string', 'max:255'],
            'promo_image_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'promo_image_remove' => ['nullable', 'boolean'],
            'newsletter_heading' => ['nullable', 'string', 'max:255'],
            'newsletter_body' => ['nullable', 'string', 'max:1000'],
            'newsletter_image_upload' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096', new ImageDimensions(minWidth: 320, minHeight: 240, maxWidth: 2400, maxHeight: 2400)],
            'newsletter_image_remove' => ['nullable', 'boolean'],
            'social_facebook' => ['nullable', 'string', 'max:255'],
            'social_instagram' => ['nullable', 'string', 'max:255'],
            'social_x' => ['nullable', 'string', 'max:255'],
            'social_pinterest' => ['nullable', 'string', 'max:255'],
        ];
    }
}
