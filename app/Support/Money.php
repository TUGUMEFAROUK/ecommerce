<?php

namespace App\Support;

use App\Services\SettingsService;

class Money
{
    /**
     * Display symbols for the most common ISO 4217 codes.
     *
     * Codes that are missing from this map fall back to the code itself,
     * so an unfamiliar currency still renders readably instead of being
     * silently mistreated as a dollar amount.
     *
     * @var array<string, string>
     */
    private const SYMBOLS = [
        'AUD' => '$',
        'BRL' => 'R$',
        'CAD' => '$',
        'CHF' => 'CHF ',
        'CNY' => '¥',
        'EUR' => '€',
        'GBP' => '£',
        'INR' => '₹',
        'JPY' => '¥',
        'KES' => 'KSh ',
        'NGN' => '₦',
        'NZD' => '$',
        'SGD' => '$',
        'UGX' => 'UGX ',
        'USD' => '$',
        'ZAR' => 'R',
    ];

    /**
     * The store's presentment currency, taken from the saved settings.
     */
    public static function currency(): string
    {
        $currency = strtoupper(app(SettingsService::class)->get('store_currency'));

        return $currency !== '' ? $currency : 'USD';
    }

    /**
     * The display symbol for a currency code.
     */
    public static function symbol(?string $currency = null): string
    {
        $currency = strtoupper($currency ?? self::currency());

        return self::SYMBOLS[$currency] ?? $currency.' ';
    }

    /**
     * The number of decimal places a currency is quoted with.
     */
    public static function decimals(?string $currency = null): int
    {
        $currency = strtoupper($currency ?? self::currency());

        return Currency::isZeroDecimal($currency) ? 0 : 2;
    }

    /**
     * Format an amount using the store currency and its own precision.
     *
     * `number_format` is used rather than the locale-aware formatter so
     * the storefront renders the same way on every host, whether or not
     * the intl extension is installed.
     */
    public static function format(float|int|string $amount, ?string $currency = null): string
    {
        $currency = strtoupper($currency ?? self::currency());

        return self::symbol($currency).number_format((float) $amount, self::decimals($currency), '.', ',');
    }

    /**
     * Format an amount prefixed with the currency code instead of a symbol.
     *
     * The admin panel shows codes rather than symbols so a figure is never
     * ambiguous when a store prices in more than one currency.
     */
    public static function formatWithCode(float|int|string $amount, ?string $currency = null): string
    {
        $currency = strtoupper($currency ?? self::currency());

        return $currency.' '.number_format((float) $amount, self::decimals($currency), '.', ',');
    }
}
