<?php

namespace App\Http\Requests;

use App\Enums\ProductStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminProductRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('products', 'slug')->ignore($this->route('product')),
            ],
            'description' => ['nullable', 'string', 'max:10000'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'sku' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'sku')->ignore($this->route('product')),
            ],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:1000000'],
            'status' => ['required', 'string', Rule::enum(ProductStatus::class)],
            'featured' => ['sometimes', 'boolean'],
        ];
    }
}
