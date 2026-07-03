<?php

namespace App\Services\Payments;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\DTOs\CreatePaymentOrderResult;
use App\Services\Payments\DTOs\VerifyPaymentResult;
use App\Services\Payments\Enums\PaymentStatus;
use App\Services\Payments\Exceptions\DuplicatePaymentException;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function createPendingPayment(Order $order, CreatePaymentOrderResult $result): Payment
    {
        $existing = Payment::query()
            ->where('gateway_order_id', $result->gatewayOrderId)
            ->where('payment_gateway', $result->gateway)
            ->where('status', PaymentStatus::Pending)
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        $payment = new Payment;
        $payment->order_id = $order->id;
        $payment->payment_gateway = $result->gateway;
        $payment->gateway_order_id = $result->gatewayOrderId;
        $payment->amount = $result->amount;
        $payment->currency = $result->currency;
        $payment->status = PaymentStatus::Pending;
        $payment->response_payload = $result->rawResponse;
        $payment->save();

        return $payment;
    }

    public function findPaidByGatewayPaymentId(string $gateway, string $gatewayPaymentId): ?Payment
    {
        return Payment::query()
            ->forGateway($gateway)
            ->where('gateway_payment_id', $gatewayPaymentId)
            ->paid()
            ->first();
    }

    public function findPendingForOrder(Order $order, string $gatewayOrderId, string $gateway): ?Payment
    {
        return Payment::query()
            ->where('order_id', $order->id)
            ->forGateway($gateway)
            ->where('gateway_order_id', $gatewayOrderId)
            ->pending()
            ->latest('id')
            ->first();
    }

    public function markPaid(Payment $payment, VerifyPaymentResult $result): void
    {
        DB::transaction(function () use ($payment, $result) {
            $payment->refresh();
            $order = $payment->order()->lockForUpdate()->first();

            if ($order === null) {
                return;
            }

            if ($order->isPaymentVerified() && $payment->isPaid()) {
                $this->syncOrderPaymentSnapshot($order, $payment);

                return;
            }

            $existingPaid = $this->findPaidByGatewayPaymentId($result->gateway, $result->gatewayPaymentId);
            if ($existingPaid !== null && $existingPaid->id !== $payment->id) {
                if ($existingPaid->order_id === $order->id) {
                    $order->payment_status = 'verified';
                    $this->syncOrderPaymentSnapshot($order, $existingPaid);

                    return;
                }

                throw DuplicatePaymentException::alreadyPaid();
            }

            $payment->gateway_payment_id = $result->gatewayPaymentId;
            $payment->signature = $result->signature;
            $payment->amount = $result->amount;
            $payment->currency = $result->currency;
            $payment->status = PaymentStatus::Paid;
            $payment->response_payload = $result->rawResponse;
            $payment->paid_at = now();
            $payment->save();

            $order->payment_status = 'verified';
            $this->syncOrderPaymentSnapshot($order, $payment);
        });
    }

    public function createPaidPayment(Order $order, VerifyPaymentResult $result): Payment
    {
        $existing = $this->findPaidByGatewayPaymentId($result->gateway, $result->gatewayPaymentId);

        if ($existing !== null) {
            $this->syncOrderPaymentSnapshot($order, $existing);

            return $existing;
        }

        $payment = new Payment;
        $payment->order_id = $order->id;
        $payment->payment_gateway = $result->gateway;
        $payment->gateway_order_id = $result->gatewayOrderId;
        $payment->gateway_payment_id = $result->gatewayPaymentId;
        $payment->signature = $result->signature;
        $payment->amount = $result->amount;
        $payment->currency = $result->currency;
        $payment->status = PaymentStatus::Paid;
        $payment->response_payload = $result->rawResponse;
        $payment->paid_at = now();
        $payment->save();

        $this->syncOrderPaymentSnapshot($order, $payment);

        return $payment;
    }

    private function syncOrderPaymentSnapshot(Order $order, Payment $payment): void
    {
        if ($payment->gateway_payment_id) {
            $order->payment_reference = $payment->gateway_payment_id;
        }

        $order->payment_amount = $payment->amount;
        $order->payment_made_at = $payment->paid_at ?? now();
        $order->save();
    }

    public function markFailed(Payment $payment, string $failureCode, ?array $metadata = null): void
    {
        $payment->status = PaymentStatus::Failed;
        $payment->failed_at = now();
        $payment->metadata = array_merge($payment->metadata ?? [], array_merge($metadata ?? [], [
            'failure_code' => $failureCode,
            'attempt_at' => now()->toIso8601String(),
        ]));
        $payment->save();
    }
}
