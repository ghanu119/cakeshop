<?php

namespace App\Messaging\Drivers;

use App\Messaging\Contracts\MessagingGateway;
use App\Messaging\Exceptions\MessageDeliveryException;
use App\Support\PhoneNormalizer;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Meta WhatsApp Cloud API driver (Graph API).
 *
 * Sends pre-approved template messages (login OTPs and order notifications).
 * All provider-specific wire details live here; callers depend only on the
 * {@see MessagingGateway} contract.
 */
class WhatsAppCloudDriver implements MessagingGateway
{
    /** Graph API payload constants. */
    private const MESSAGING_PRODUCT = 'whatsapp';

    private const RECIPIENT_TYPE = 'individual';

    private const MESSAGE_TYPE = 'template';

    private const PARAM_TYPE_TEXT = 'text';

    /** Fallbacks used only when config is missing. */
    private const DEFAULT_OTP_TEMPLATE = 'login_otp';

    private const DEFAULT_LANGUAGE = 'en_US';

    private const DEFAULT_COUNTRY_CODE = '91';

    private const REQUEST_TIMEOUT_SECONDS = 15;

    /** Meta error codes that mean the recipient cannot receive the message. */
    private const UNDELIVERABLE_CODES = [131026, 131030, 131047, 131049, 131050];

    /** Meta error codes that mean the request/recipient number was invalid. */
    private const INVALID_NUMBER_CODES = [100, 131008, 131009];

    /**
     * @param  array<string, mixed>  $config
     */
    public function __construct(private readonly array $config) {}

    public function isEnabled(): bool
    {
        return $this->boolConfig('enabled')
            && $this->stringConfig('phone_number_id') !== ''
            && $this->stringConfig('access_token') !== '';
    }

    public function sendOtp(string $phone, string $code): void
    {
        // Authentication-style templates repeat the code across each body
        // variable; the count is configurable to match the approved template.
        $bodyParams = array_fill(0, $this->otpBodyParamCount(), $code);

        $this->dispatchTemplate(
            $phone,
            $this->stringConfig('otp_template', self::DEFAULT_OTP_TEMPLATE),
            $this->stringConfig('otp_template_lang', self::DEFAULT_LANGUAGE),
            $bodyParams,
            $this->otpButtonComponents($code),
        );
    }

    public function sendTemplate(
        string $phone,
        string $template,
        string $lang,
        array $bodyParams = [],
        ?string $dynamicUrlButtonSuffix = null,
    ): void {
        $this->dispatchTemplate(
            $phone,
            $template,
            $lang,
            array_values($bodyParams),
            $this->urlButtonComponents($dynamicUrlButtonSuffix),
        );
    }

    /**
     * Assemble the template payload and hand it to the transport.
     *
     * @param  array<int, string|int|float>  $bodyParams
     * @param  array<int, array<string, mixed>>  $extraComponents
     */
    private function dispatchTemplate(
        string $phone,
        string $template,
        string $lang,
        array $bodyParams,
        array $extraComponents = [],
    ): void {
        $components = [];

        if ($bodyParams !== []) {
            $components[] = [
                'type' => 'body',
                'parameters' => array_map($this->textParameter(...), $bodyParams),
            ];
        }

        $this->send($phone, [
            'name' => $template,
            'language' => ['code' => $lang],
            'components' => array_merge($components, $extraComponents),
        ]);
    }

    /**
     * @param  array<string, mixed>  $template
     */
    private function send(string $phone, array $template): void
    {
        if (! $this->isEnabled()) {
            throw new MessageDeliveryException(
                MessageDeliveryException::REASON_DISABLED,
                'WhatsApp messaging is not enabled.'
            );
        }

        $recipient = $this->resolveRecipient($phone);

        if ($recipient === null) {
            throw new MessageDeliveryException(
                MessageDeliveryException::REASON_INVALID_NUMBER,
                'The provided phone number is invalid.'
            );
        }

        $response = $this->postTemplate($recipient, $template);

        $this->assertSuccessful($response);
    }

    /**
     * @param  array<string, mixed>  $template
     */
    private function postTemplate(string $recipient, array $template): Response
    {
        try {
            return Http::withToken($this->stringConfig('access_token'))
                ->acceptJson()
                ->asJson()
                ->timeout(self::REQUEST_TIMEOUT_SECONDS)
                ->post($this->endpoint(), [
                    'messaging_product' => self::MESSAGING_PRODUCT,
                    'recipient_type' => self::RECIPIENT_TYPE,
                    'to' => $recipient,
                    'type' => self::MESSAGE_TYPE,
                    'template' => $template,
                ]);
        } catch (Throwable $e) {
            Log::warning('WhatsApp send failed (transport).', ['error' => $e->getMessage()]);

            throw new MessageDeliveryException(
                MessageDeliveryException::REASON_PROVIDER_ERROR,
                'Could not reach the WhatsApp service.',
                $e
            );
        }
    }

    private function assertSuccessful(Response $response): void
    {
        if ($response->successful() && ! $response->json('error')) {
            return;
        }

        $errorCode = (int) $response->json('error.code', 0);
        $errorMessage = (string) $response->json('error.message', 'WhatsApp delivery failed.');

        Log::warning('WhatsApp send failed (api).', [
            'status' => $response->status(),
            'error_code' => $errorCode,
            'error' => $errorMessage,
        ]);

        throw new MessageDeliveryException($this->mapErrorReason($errorCode), $errorMessage);
    }

    private function endpoint(): string
    {
        return rtrim($this->stringConfig('api_url'), '/')
            .'/'.$this->stringConfig('phone_number_id').'/messages';
    }

    private function resolveRecipient(string $phone): ?string
    {
        // Test mode: deliver to the fixed allowed number regardless of the input.
        $target = $this->boolConfig('test_mode')
            ? $this->stringConfig('test_number')
            : $phone;

        return PhoneNormalizer::toE164($target, $this->stringConfig('default_country_code', self::DEFAULT_COUNTRY_CODE));
    }

    /**
     * @return array<string, string>
     */
    private function textParameter(string|int|float $value): array
    {
        return ['type' => self::PARAM_TYPE_TEXT, 'text' => (string) $value];
    }

    private function otpBodyParamCount(): int
    {
        return max(1, $this->intConfig('otp_body_params', 1));
    }

    /**
     * Optional copy-code button for WhatsApp authentication templates.
     *
     * @return array<int, array<string, mixed>>
     */
    private function otpButtonComponents(string $code): array
    {
        if (! $this->boolConfig('otp_copy_code_button')) {
            return [];
        }

        return $this->urlButtonComponents($code, '0');
    }

    /**
     * Dynamic URL button for approved WhatsApp templates (order detail links, etc.).
     *
     * @return array<int, array<string, mixed>>
     */
    private function urlButtonComponents(?string $suffix, ?string $index = null): array
    {
        if ($suffix === null || $suffix === '') {
            return [];
        }

        if ($index === null) {
            if (! $this->boolConfig('order_url_button')) {
                return [];
            }

            $index = $this->stringConfig('order_url_button_index', '0');
        }

        return [[
            'type' => 'button',
            'sub_type' => 'url',
            'index' => $index,
            'parameters' => [$this->textParameter($suffix)],
        ]];
    }

    private function mapErrorReason(int $code): string
    {
        return match (true) {
            in_array($code, self::UNDELIVERABLE_CODES, true) => MessageDeliveryException::REASON_UNDELIVERABLE,
            in_array($code, self::INVALID_NUMBER_CODES, true) => MessageDeliveryException::REASON_INVALID_NUMBER,
            default => MessageDeliveryException::REASON_PROVIDER_ERROR,
        };
    }

    private function stringConfig(string $key, string $default = ''): string
    {
        $value = $this->config[$key] ?? null;

        return is_scalar($value) ? (string) $value : $default;
    }

    private function intConfig(string $key, int $default = 0): int
    {
        $value = $this->config[$key] ?? null;

        return is_numeric($value) ? (int) $value : $default;
    }

    private function boolConfig(string $key): bool
    {
        return (bool) ($this->config[$key] ?? false);
    }
}
