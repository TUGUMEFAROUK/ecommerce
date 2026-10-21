<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class AdminProductImageRequest extends FormRequest
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
     * MIME types are sniffed from the file content by the mimes rule, so
     * the extension alone cannot smuggle another file type through. SVG is
     * deliberately excluded: SVGs are XML documents that can embed scripts,
     * which would be a stored-XSS vector when served from the public disk.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'image' => ['required', 'file', 'max:2048', 'mimes:jpg,jpeg,png,webp'],
        ];
    }
}
