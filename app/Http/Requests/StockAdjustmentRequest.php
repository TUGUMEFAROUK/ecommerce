<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StockAdjustmentRequest extends FormRequest
{
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
     * The client sends the absolute resulting stock level; the signed
     * change is always computed on the server from the stored value.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }
}
