<?php

namespace Tests\Fakes;

use App\Messaging\Contracts\MessagingGateway;
use App\Messaging\Exceptions\MessageDeliveryException;

class FakeMessagingGateway implements MessagingGateway
{
    public bool $enabled = true;

    public ?string $throwReason = null;

    /** @var array<int, array{phone: string, code: string}> */
    public array $otps = [];

    /** @var array<int, array{phone: string, template: string, lang: string, params: array, url_button_suffix: ?string}> */
    public array $templates = [];

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function sendOtp(string $phone, string $code): void
    {
        if ($this->throwReason !== null) {
            throw new MessageDeliveryException($this->throwReason, 'Fake delivery failure.');
        }

        $this->otps[] = ['phone' => $phone, 'code' => $code];
    }

    public function sendTemplate(
        string $phone,
        string $template,
        string $lang,
        array $bodyParams = [],
        ?string $dynamicUrlButtonSuffix = null,
    ): void {
        if ($this->throwReason !== null) {
            throw new MessageDeliveryException($this->throwReason, 'Fake delivery failure.');
        }

        $this->templates[] = [
            'phone' => $phone,
            'template' => $template,
            'lang' => $lang,
            'params' => $bodyParams,
            'url_button_suffix' => $dynamicUrlButtonSuffix,
        ];
    }

    public function lastOtpCode(): ?string
    {
        $last = end($this->otps);

        return $last['code'] ?? null;
    }
}
