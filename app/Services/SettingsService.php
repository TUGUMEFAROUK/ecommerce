<?php

namespace App\Services;

use App\Models\Setting;
use App\Support\Money;
use Illuminate\Support\Facades\Cache;

class SettingsService
{
    private const CACHE_KEY = 'store.settings';

    /**
     * Default value for every supported setting key.
     *
     * Defaults are read at call time so the application name always follows
     * `config('app.name')` until a store name is explicitly saved.
     *
     * @return array<string, string>
     */
    public static function defaults(): array
    {
        return [
            'store_name' => (string) config('app.name', 'Laravel'),
            'store_tagline' => 'Thoughtfully sourced, beautifully made, designed to last.',
            'store_logo' => '',
            'brand_display' => 'name',
            'store_logo_height' => '36',
            'store_currency' => 'USD',
            'contact_email' => '',
            'contact_phone' => '',
            'shipping_flat_fee' => '5.99',
            'shipping_free_threshold' => '75.00',
            'delivery_estimate' => '2–5 business days',
            'low_stock_threshold' => '10',
            'returns_window_days' => '30',
            'announcement_enabled' => '1',
            'announcement_text' => '',
            'announcement_link' => '',
            'hero_eyebrow' => 'Timeless essentials',
            'hero_heading' => 'Everything you need, delivered to your door',
            'hero_body' => 'Browse electronics, home essentials, books and more from our catalogue of quality products.',
            'hero_cta_label' => 'Browse all products',
            'hero_cta_url' => '',
            'hero_image' => '',
            'promo_eyebrow' => 'The edit',
            'promo_heading' => 'Objects of quiet refinement',
            'promo_body' => 'A considered edit of materials, form and function. Subtle details, enduring quality and a sense of ease in every piece.',
            'promo_cta_label' => 'Discover the edit',
            'promo_cta_url' => '',
            'promo_image' => '',
            'newsletter_heading' => 'Join the journal',
            'newsletter_body' => 'Receive seasonal edits, new arrivals and quiet inspiration, straight to your inbox.',
            'newsletter_image' => '',
            'social_facebook' => '',
            'social_instagram' => '',
            'social_x' => '',
            'social_pinterest' => '',
        ];
    }

    /**
     * Get a single setting value, falling back to its default.
     */
    public function get(string $key): string
    {
        return $this->all()[$key] ?? '';
    }

    /**
     * All supported settings merged over their defaults.
     *
     * Only the stored rows are cached; defaults are merged on every read.
     *
     * @return array<string, string>
     */
    public function all(): array
    {
        $stored = Cache::rememberForever(self::CACHE_KEY, fn (): array => Setting::query()
            ->pluck('value', 'key')
            ->all());

        $values = [];

        foreach (self::defaults() as $key => $default) {
            $value = $stored[$key] ?? null;

            $values[$key] = is_string($value) ? $value : $default;
        }

        return $values;
    }

    /**
     * Persist known settings and invalidate the cached rows.
     *
     * @param  array<string, mixed>  $values
     */
    public function set(array $values): void
    {
        foreach ($values as $key => $value) {
            if (! array_key_exists($key, self::defaults())) {
                continue;
            }

            Setting::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }

        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Whether the storefront shows the company logo or the store name.
     *
     * The two are mutually exclusive: a store either leads with its mark
     * or with its name, never with both side by side.
     */
    public function brandDisplay(): string
    {
        return $this->get('brand_display') === 'logo' ? 'logo' : 'name';
    }

    /**
     * Whether the storefront leads with the company logo.
     *
     * A logo can only be shown when one has actually been uploaded, so a
     * store that picks the logo without uploading a file falls back to
     * its name rather than showing an empty space.
     */
    public function usesLogo(): bool
    {
        return $this->brandDisplay() === 'logo' && $this->get('store_logo') !== '';
    }

    /**
     * The rendered height of the company logo, in pixels.
     *
     * The value is clamped so a logo can never be so small that it becomes
     * unreadable, nor so large that it breaks the header it sits in.
     */
    public function logoHeight(): int
    {
        return max(self::MIN_LOGO_HEIGHT, min(self::MAX_LOGO_HEIGHT, (int) $this->get('store_logo_height')));
    }

    /**
     * The smallest height the company logo may be rendered at.
     */
    public const MIN_LOGO_HEIGHT = 16;

    /**
     * The largest height the company logo may be rendered at.
     */
    public const MAX_LOGO_HEIGHT = 64;

    /**
     * The flat shipping fee in integer cents.
     */
    public function shippingFlatFeeCents(): int
    {
        return $this->toCents($this->get('shipping_flat_fee'));
    }

    /**
     * The free-shipping threshold in integer cents.
     */
    public function shippingFreeThresholdCents(): int
    {
        return $this->toCents($this->get('shipping_free_threshold'));
    }

    /**
     * The stock level at or below which products count as low stock.
     */
    public function lowStockThreshold(): int
    {
        return max(0, (int) $this->get('low_stock_threshold'));
    }

    /**
     * The number of days a customer has to return an item.
     */
    public function returnsWindowDays(): int
    {
        return max(0, (int) $this->get('returns_window_days'));
    }

    /**
     * Whether the announcement bar should be rendered.
     */
    public function announcementEnabled(): bool
    {
        return $this->get('announcement_enabled') !== '0';
    }

    /**
     * The announcement message.
     *
     * When no custom message has been saved the bar falls back to a
     * sentence built from the store's own free-shipping threshold, so the
     * message always reflects the real shipping policy.
     */
    public function announcementText(): string
    {
        $text = trim($this->get('announcement_text'));

        if ($text !== '') {
            return $text;
        }

        return 'Complimentary delivery on orders over '.Money::format($this->shippingFreeThresholdCents() / 100).'.';
    }

    /**
     * The social profiles that have been configured, keyed by network name.
     *
     * @return array<string, string>
     */
    public function socialLinks(): array
    {
        $networks = [
            'Facebook' => $this->get('social_facebook'),
            'Instagram' => $this->get('social_instagram'),
            'X' => $this->get('social_x'),
            'Pinterest' => $this->get('social_pinterest'),
        ];

        $links = [];

        foreach ($networks as $network => $url) {
            $url = trim($url);

            if ($url !== '') {
                $links[$network] = $url;
            }
        }

        return $links;
    }

    private function toCents(string $amount): int
    {
        return max(0, (int) round(((float) $amount) * 100));
    }
}
