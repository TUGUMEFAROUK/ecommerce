<?php

namespace App\Http\Requests;

use App\Enums\OrderStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminOrderStatusRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Authorization also happens against OrderPolicy::update on the route;
     * this guard covers the request itself if the middleware were missing.
     */
    public function authorize(): bool
    {
        return (bool) $this->user()?->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * Whether the transition is legal for this specific order is decided
     * by OrderStatusService, not by these rules.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', Rule::enum(OrderStatus::class)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
