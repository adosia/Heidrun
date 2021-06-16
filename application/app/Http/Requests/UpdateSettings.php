<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettings extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'api_access_token' => ['required', 'min:16', function($attribute, $value, $fail) {
                if (!$this->isSecureApiAccessToken($value)) {
                    $fail('This api access token is not secure.');
                }
            }]
        ];
    }

    /**
     * @param $value
     * @return bool
     */
    private function isSecureApiAccessToken($value): bool
    {
        if (
            preg_match('/[a-z]/', $value) && // Contains lowercase letters
            preg_match('/[A-Z]/', $value)    // Contains uppercase letters
        ) {
            $passedCount = 0;
            if (preg_match('/[0-9]/', $value)) { $passedCount++; }        // Contains numbers
            if (preg_match('/[^a-zA-Z0-9]/', $value)) { $passedCount++; } // Contains symbols
            if ($passedCount == 2) {
                return true;
            }
        }
        return false;
    }
}
