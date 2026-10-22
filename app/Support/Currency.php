<?php

namespace App\Support;

/**
 * The currencies the store is allowed to price in.
 *
 * The list is the single source of truth for three things that used to drift
 * apart: the settings form's currency picker, the validation rule that keeps
 * a mistyped code out of pricing and payment configuration, and the way an
 * amount is formatted for display.
 */
class Currency
{
    /**
     * ISO 4217 alphabetic codes with their English currency names.
     *
     * @var array<string, string>
     */
    private const NAMES = [
        'AED' => 'UAE Dirham',
        'AFN' => 'Afghan Afghani',
        'ALL' => 'Albanian Lek',
        'AMD' => 'Armenian Dram',
        'ANG' => 'Netherlands Antillean Guilder',
        'AOA' => 'Angolan Kwanza',
        'ARS' => 'Argentine Peso',
        'AUD' => 'Australian Dollar',
        'AWN' => 'Aruban Florin',
        'AZN' => 'Azerbaijani Manat',
        'BAM' => 'Bosnia-Herzegovina Convertible Mark',
        'BBD' => 'Barbadian Dollar',
        'BDT' => 'Bangladeshi Taka',
        'BGN' => 'Bulgarian Lev',
        'BHD' => 'Bahraini Dinar',
        'BIF' => 'Burundian Franc',
        'BMD' => 'Bermudian Dollar',
        'BND' => 'Brunei Dollar',
        'BOB' => 'Bolivian Boliviano',
        'BRL' => 'Brazilian Real',
        'BSD' => 'Bahamian Dollar',
        'BTN' => 'Bhutanese Ngultrum',
        'BWP' => 'Botswana Pula',
        'BYN' => 'Belarusian Ruble',
        'BZD' => 'Belize Dollar',
        'CAD' => 'Canadian Dollar',
        'CDF' => 'Congolese Franc',
        'CHF' => 'Swiss Franc',
        'CLP' => 'Chilean Peso',
        'CNY' => 'Chinese Yuan',
        'COP' => 'Colombian Peso',
        'CRC' => 'Costa Rican Colón',
        'CUP' => 'Cuban Peso',
        'CVE' => 'Cape Verdean Escudo',
        'CZK' => 'Czech Koruna',
        'DJF' => 'Djiboutian Franc',
        'DKK' => 'Danish Krone',
        'DOP' => 'Dominican Peso',
        'DZD' => 'Algerian Dinar',
        'EGP' => 'Egyptian Pound',
        'ERN' => 'Eritrean Nakfa',
        'ETB' => 'Ethiopian Birr',
        'EUR' => 'Euro',
        'FJD' => 'Fiji Dollar',
        'FKP' => 'Falkland Islands Pound',
        'GBP' => 'British Pound',
        'GEL' => 'Georgian Lari',
        'GHS' => 'Ghanaian Cedi',
        'GIP' => 'Gibraltar Pound',
        'GMD' => 'Gambian Dalasi',
        'GNF' => 'Guinean Franc',
        'GTQ' => 'Guatemalan Quetzal',
        'GYD' => 'Guyanese Dollar',
        'HKD' => 'Hong Kong Dollar',
        'HNL' => 'Honduran Lempira',
        'HTG' => 'Haitian Gourde',
        'HUF' => 'Hungarian Forint',
        'IDR' => 'Indonesian Rupiah',
        'ILS' => 'Israeli New Shekel',
        'INR' => 'Indian Rupee',
        'IQD' => 'Iraqi Dinar',
        'IRR' => 'Iranian Rial',
        'ISK' => 'Icelandic Króna',
        'JMD' => 'Jamaican Dollar',
        'JOD' => 'Jordanian Dinar',
        'JPY' => 'Japanese Yen',
        'KES' => 'Kenyan Shilling',
        'KGS' => 'Kyrgyzstani Som',
        'KHR' => 'Cambodian Riel',
        'KMF' => 'Comorian Franc',
        'KPW' => 'North Korean Won',
        'KRW' => 'South Korean Won',
        'KWD' => 'Kuwaiti Dinar',
        'KYD' => 'Cayman Islands Dollar',
        'KZT' => 'Kazakhstani Tenge',
        'LAK' => 'Laotian Kip',
        'LBP' => 'Lebanese Pound',
        'LKR' => 'Sri Lankan Rupee',
        'LRD' => 'Liberian Dollar',
        'LSL' => 'Lesotho Loti',
        'LYD' => 'Libyan Dinar',
        'MAD' => 'Moroccan Dirham',
        'MDL' => 'Moldovan Leu',
        'MGA' => 'Malagasy Ariary',
        'MKD' => 'Macedonian Denar',
        'MMK' => 'Myanmar Kyat',
        'MNT' => 'Mongolian Tugrik',
        'MOP' => 'Macanese Pataca',
        'MRU' => 'Mauritanian Ouguiya',
        'MUR' => 'Mauritian Rupee',
        'MVR' => 'Maldivian Rufiyaa',
        'MWK' => 'Malawian Kwacha',
        'MXN' => 'Mexican Peso',
        'MYR' => 'Malaysian Ringgit',
        'MZN' => 'Mozambican Metical',
        'NAD' => 'Namibian Dollar',
        'NGN' => 'Nigerian Naira',
        'NIO' => 'Nicaraguan Córdoba',
        'NOK' => 'Norwegian Krone',
        'NPR' => 'Nepalese Rupee',
        'NZD' => 'New Zealand Dollar',
        'OMR' => 'Omani Rial',
        'PAB' => 'Panamanian Balboa',
        'PEN' => 'Peruvian Sol',
        'PGK' => 'Papua New Guinean Kina',
        'PHP' => 'Philippine Peso',
        'PKR' => 'Pakistani Rupee',
        'PLN' => 'Polish Zloty',
        'PYG' => 'Paraguayan Guarani',
        'QAR' => 'Qatari Rial',
        'RON' => 'Romanian Leu',
        'RSD' => 'Serbian Dinar',
        'RUB' => 'Russian Ruble',
        'RWF' => 'Rwandan Franc',
        'SAR' => 'Saudi Riyal',
        'SBD' => 'Solomon Islands Dollar',
        'SCR' => 'Seychellois Rupee',
        'SDG' => 'Sudanese Pound',
        'SEK' => 'Swedish Krona',
        'SGD' => 'Singapore Dollar',
        'SHP' => 'Saint Helena Pound',
        'SLE' => 'Sierra Leonean Leone',
        'SOS' => 'Somali Shilling',
        'SRD' => 'Surinamese Dollar',
        'SSP' => 'South Sudanese Pound',
        'STN' => 'São Tomé and Príncipe Dobra',
        'SVC' => 'Salvadoran Colón',
        'SYP' => 'Syrian Pound',
        'SZL' => 'Swazi Lilangeni',
        'THB' => 'Thai Baht',
        'TJS' => 'Tajikistani Somoni',
        'TMT' => 'Turkmenistani Manat',
        'TND' => 'Tunisian Dinar',
        'TOP' => 'Tongan Paʻanga',
        'TRY' => 'Turkish Lira',
        'TTD' => 'Trinidad and Tobago Dollar',
        'TWD' => 'New Taiwan Dollar',
        'TZS' => 'Tanzanian Shilling',
        'UAH' => 'Ukrainian Hryvnia',
        'UGX' => 'Ugandan Shilling',
        'USD' => 'US Dollar',
        'UYU' => 'Uruguayan Peso',
        'UZS' => 'Uzbekistani Som',
        'VES' => 'Venezuelan Bolívar',
        'VND' => 'Vietnamese Dong',
        'VUV' => 'Vanuatu Vatu',
        'WST' => 'Samoan Tala',
        'XAF' => 'Central African CFA Franc',
        'XCD' => 'East Caribbean Dollar',
        'XOF' => 'West African CFA Franc',
        'XPF' => 'CFP Franc',
        'YER' => 'Yemeni Rial',
        'ZAR' => 'South African Rand',
        'ZMW' => 'Zambian Kwacha',
        'ZWL' => 'Zimbabwean Dollar',
    ];

    /**
     * Currencies that are quoted without minor units.
     *
     * Shillings, yen and francs are counted in whole units in practice, so
     * showing "UGX 12,000" is correct where "UGX 12,000.00" is noise.
     *
     * @var list<string>
     */
    private const ZERO_DECIMAL = [
        'BIF', 'CLP', 'DJF', 'GNF', 'ISK', 'JPY', 'KMF', 'KRW', 'PYG',
        'RWF', 'UGX', 'VND', 'VUV', 'XAF', 'XOF', 'XPF',
    ];

    /**
     * Every supported currency, keyed by its code, sorted by name.
     *
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = self::NAMES;

        asort($options);

        return $options;
    }

    /**
     * The supported currency codes.
     *
     * @return list<string>
     */
    public static function codes(): array
    {
        return array_keys(self::NAMES);
    }

    /**
     * Whether the given code is a supported currency.
     */
    public static function supports(string $code): bool
    {
        return isset(self::NAMES[strtoupper(trim($code))]);
    }

    /**
     * The display name of a currency, falling back to its code.
     */
    public static function name(string $code): string
    {
        $code = strtoupper(trim($code));

        return self::NAMES[$code] ?? $code;
    }

    /**
     * Whether the currency is quoted without minor units.
     */
    public static function isZeroDecimal(string $code): bool
    {
        return in_array(strtoupper(trim($code)), self::ZERO_DECIMAL, true);
    }
}
