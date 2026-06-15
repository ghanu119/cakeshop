<?php

namespace App\Http\Requests\Account;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

abstract class AccountFormRequest extends FormRequest
{
    protected function failedValidation(Validator $validator): void
    {
        throw (new ValidationException($validator))
            ->redirectTo($this->accountValidationRedirectUrl());
    }

    protected function accountValidationRedirectUrl(): string
    {
        return route('account.login');
    }
}
