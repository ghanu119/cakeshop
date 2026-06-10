<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StorePushSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && ($user->hasRole('Admin') || $user->hasRole('Kitchen'));
    }

    public function rules(): array
    {
        return [
            'endpoint' => ['required', 'string', 'max:500', 'url', 'regex:/^https:\/\//i'],
            'keys' => ['required', 'array'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $endpoint = (string) $this->input('endpoint', '');

            if ($endpoint !== '' && ! $this->isKnownPushEndpoint($endpoint)) {
                $validator->errors()->add('endpoint', __('Invalid push subscription endpoint.'));
            }
        });
    }

    private function isKnownPushEndpoint(string $endpoint): bool
    {
        $allowedHosts = [
            'fcm.googleapis.com',
            'updates.push.services.mozilla.com',
            'updates-autopush.stage.mozaws.net',
            'wns2-pn1p.notify.windows.com',
            'notify.windows.com',
            'web.push.apple.com',
            'push.apple.com',
        ];

        $host = parse_url($endpoint, PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return false;
        }

        foreach ($allowedHosts as $allowed) {
            if ($host === $allowed || str_ends_with($host, '.'.$allowed)) {
                return true;
            }
        }

        return str_ends_with($host, '.push.services.mozilla.com')
            || str_ends_with($host, '.notify.windows.com');
    }
}
