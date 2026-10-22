<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class NewsletterSubscriptionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * The newsletter form is public: anyone may subscribe.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Normalise the address before validation so "Ada@Example.com" and
     * "ada@example.com" are treated as the same subscriber.
     */
    protected function prepareForValidation(): void
    {
        $email = $this->input('newsletter_email');

        if (is_string($email)) {
            $this->merge(['newsletter_email' => mb_strtolower(trim($email))]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * The field is namespaced so a newsletter error can never be confused
     * with the `email` field used by the sign-in and registration forms.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'newsletter_email' => ['required', 'string', 'email', 'max:255', 'unique:newsletter_subscribers,email'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'newsletter_email.required' => 'Please enter your email address.',
            'newsletter_email.email' => 'Please enter a valid email address.',
            'newsletter_email.unique' => 'That email address is already subscribed to the journal.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'newsletter_email' => 'email address',
        ];
    }
}
