<?php

namespace App\Services;

use App\Models\Setting;
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
            'store_currency' => 'USD',
            'contact_email' => '',
            'contact_phone' => '',
            'shipping_flat_fee' => '5.99',
            'shipping_free_threshold' => '75.00',
            'low_stock_threshold' => '10',
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

    private function toCents(string $amount): int
    {
        return max(0, (int) round(((float) $amount) * 100));
    }
}
