<?php

declare(strict_types=1);

namespace App\Subscription\Test\Unit\Entity\Payment;

use App\Subscription\Entity\Payment\Amount;
use App\Subscription\Entity\Payment\ExternalId;
use App\Subscription\Entity\Payment\Id;
use App\Subscription\Entity\Payment\Payment;
use App\Subscription\Entity\Payment\PaymentStatus;
use App\Subscription\Entity\Subscription\Plan;
use App\Subscription\Entity\Subscription\UserId;
use App\Subscription\Event\PaymentConfirmed;
use DomainException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class PaymentTest extends TestCase
{
    public function testConfirmMovesPaymentToSucceededAndRecordsEvent(): void
    {
        $payment = $this->createPendingPayment();

        $payment->confirm();

        self::assertSame(PaymentStatus::SUCCEEDED, $payment->getStatus());
        self::assertNotNull($payment->getConfirmedAt());

        $events = $payment->releaseEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(PaymentConfirmed::class, $events[0]);
    }

    public function testConfirmIsIdempotent(): void
    {
        $payment = $this->createPendingPayment();
        $payment->confirm();
        $payment->confirm();

        self::assertSame(PaymentStatus::SUCCEEDED, $payment->getStatus());
        self::assertCount(1, $payment->releaseEvents());
    }

    public function testFailedPaymentCannotBeConfirmed(): void
    {
        $payment = $this->createPendingPayment();
        $payment->fail();

        $this->expectException(DomainException::class);
        $payment->confirm();
    }

    private function createPendingPayment(): Payment
    {
        return Payment::createPending(
            Id::generate(),
            new ExternalId('external-payment-id'),
            new UserId('123e4567-e89b-12d3-a456-426614174000'),
            Plan::BASIC,
            Amount::fromRubles('490.00'),
            30,
        );
    }
}
