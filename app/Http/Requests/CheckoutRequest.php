<?php

namespace App\Http\Requests;

use App\Payments\PaymentGatewayManager;
use App\Services\SettingsService;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Validator;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'contact_name' => ['required', 'string', 'max:255'],
            'contact_email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'contact_phone' => ['required', 'string', 'max:30'],
            'shipping_line1' => ['required', 'string', 'max:255'],
            'shipping_line2' => ['nullable', 'string', 'max:255'],
            'shipping_city' => ['required', 'string', 'max:255'],
            'shipping_state' => ['required', 'string', 'max:100'],
            'shipping_postal_code' => ['required', 'string', 'max:20'],
            'shipping_country' => ['required', 'string', 'max:100'],
            'payment_method' => ['required', 'string', 'in:cod,flutterwave'],
        ];
    }

    /**
     * Configure the validator after the base rules ran.
     *
     * A requested gateway that is unavailable is a hard error — the order
     * is never silently switched to another method.
     *
     * @return list<callable(Validator): void>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->input('payment_method') !== 'flutterwave') {
                    return;
                }

                $gateway = app(PaymentGatewayManager::class)->driver('flutterwave');

                if (! $gateway->isConfigured()) {
                    $validator->errors()->add(
                        'payment_method',
                        'Online card payments are not configured for this store yet.',
                    );

                    return;
                }

                $currency = Str::upper(app(SettingsService::class)->get('store_currency'));

                if (! $gateway->supportsCurrency($currency)) {
                    $validator->errors()->add(
                        'payment_method',
                        sprintf('Online card payments are not available in %s.', $currency),
                    );
                }
            },
        ];
    }
}
