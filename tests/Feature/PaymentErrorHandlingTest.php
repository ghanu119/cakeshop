<?php

namespace Tests\Feature;

use App\Services\Payments\Exceptions\PaymentException;
use App\Services\Payments\PaymentErrorMapper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentErrorHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_error_mapper_never_returns_raw_exception_message(): void
    {
        $mapper = app(PaymentErrorMapper::class);
        $mapped = $mapper->map(new PaymentException(PaymentException::CODE_SIGNATURE_INVALID));

        $this->assertSame(__('payments.errors.signature_invalid'), $mapped['message']);
        $this->assertSame('signature_invalid', $mapped['code']);
    }

    public function test_unknown_throwable_maps_to_generic_message(): void
    {
        $mapper = app(PaymentErrorMapper::class);
        $mapped = $mapper->map(new \RuntimeException('SQLSTATE internal error'));

        $this->assertSame(__('payments.errors.unknown'), $mapped['message']);
        $this->assertStringNotContainsString('SQLSTATE', $mapped['message']);
    }
}
