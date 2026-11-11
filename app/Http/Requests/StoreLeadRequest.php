<?php

namespace App\Http\Requests;

use App\Support\Phone;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;

class StoreLeadRequest extends FormRequest
{
    /** Submissions faster than this after the form was rendered are treated as bots. */
    public const MIN_SECONDS = 3;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'phone' => Phone::digits($this->input('phone')),
            'name' => trim(strip_tags((string) $this->input('name'))),
            'zip' => trim((string) $this->input('zip')),
            'city_name' => trim(strip_tags((string) $this->input('city_name'))),
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:100'],
            'phone' => ['required', 'digits:10', 'not_regex:/^(\d)\1{9}$/'],
            'email' => ['nullable', 'email:rfc', 'max:150'],
            'zip' => ['required', 'regex:/^\d{5}$/'],
            'service_id' => ['nullable', Rule::exists('services', 'id')->where('is_published', true)],
            'state_id' => ['nullable', Rule::exists('states', 'id')],
            'city_name' => ['nullable', 'string', 'max:100'],
            'message' => ['nullable', 'string', 'max:2000'],
            'consent' => ['accepted'],
            // spam traps
            'company_website' => ['prohibited'],
            'form_token' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.digits' => 'Enter a 10-digit U.S. phone number.',
            'phone.not_regex' => 'Enter a valid phone number.',
            'zip.regex' => 'Enter a 5-digit ZIP code.',
            'consent.accepted' => 'Please confirm you agree to be contacted about your request.',
            'company_website.prohibited' => 'Your request could not be submitted.',
            'form_token.required' => 'Your session expired. Please reload the page and try again.',
        ];
    }

    public function attributes(): array
    {
        return ['zip' => 'ZIP code', 'service_id' => 'service', 'state_id' => 'state', 'city_name' => 'city'];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            try {
                $renderedAt = (int) Crypt::decryptString((string) $this->input('form_token'));
            } catch (\Throwable) {
                $validator->errors()->add('form_token', 'Your session expired. Please reload the page and try again.');

                return;
            }

            $age = time() - $renderedAt;
            if ($age < self::MIN_SECONDS || $age > 60 * 60 * 6) {
                $validator->errors()->add('form_token', 'Please take a moment to review your details, then submit again.');
            }
        });
    }

    public static function token(): string
    {
        return Crypt::encryptString((string) time());
    }
}
