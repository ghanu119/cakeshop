<?php

namespace App\Services\Payments;

use App\Services\Payments\Exceptions\PaymentException;
use Throwable;

class PaymentErrorMapper
{
    /**
     * @return array{message: string, retryable: bool, code: string}
     */
    public function map(Throwable $throwable): array
    {
        if ($throwable instanceof PaymentException) {
            $code = $throwable->customerCode();

            return [
                'message' => $this->messageForCode($code),
                'retryable' => $throwable->isRetryable(),
                'code' => $code,
            ];
        }

        return [
            'message' => $this->messageForCode(PaymentException::CODE_UNKNOWN),
            'retryable' => true,
            'code' => PaymentException::CODE_UNKNOWN,
        ];
    }

    public function messageForCode(string $code): string
    {
        $key = 'payments.errors.'.$code;
        $message = __($key);

        if ($message === $key) {
            return __( 'payments.errors.unknown');
        }

        return $message;
    }

    public function frontendMessage(string $code): string
    {
        return $this->messageForCode($code);
    }
}
