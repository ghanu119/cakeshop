<?php

namespace App\Http\Requests\Account;

class VerifyOtpRequest extends AccountFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
            'code' => ['required', 'string', 'digits:6'],
        ];
    }

    protected function accountValidationRedirectUrl(): string
    {
        $email = $this->input('email');

        return is_string($email) && $email !== ''
            ? route('account.verify-otp', ['email' => $email])
            : route('account.login');
    }
}
