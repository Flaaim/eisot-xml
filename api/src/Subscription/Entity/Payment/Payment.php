<?php

declare(strict_types=1);

namespace App\Subscription\Entity\Payment;

use App\SharedDomain\AggregateRoot;
use App\SharedDomain\Event\EventTrait;
use App\Subscription\Entity\Subscription\Plan;
use App\Subscription\Entity\Subscription\UserId;
use App\Subscription\Event\PaymentConfirmed;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use DomainException;

#[ORM\Entity]
#[ORM\Table(name: 'subscription_payments')]
final class Payment implements AggregateRoot
{
    use EventTrait;

    #[ORM\Column(name: 'amount_value', type: 'decimal', precision: 10, scale: 2)]
    private string $amountValue;

    #[ORM\Column(name: 'amount_currency', type: 'string', length: 3)]
    private string $amountCurrency;

    #[ORM\Column(name: 'duration_days', type: 'integer')]
    private int $durationDays;

    #[ORM\Column(name: 'created_at', type: 'datetime_immutable')]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(name: 'confirmed_at', type: 'datetime_immutable', nullable: true)]
    private ?DateTimeImmutable $confirmedAt = null;

    private function __construct(
        #[ORM\Id]
        #[ORM\Column(type: 'subscription_payment_id')]
        private Id $id,
        #[ORM\Column(name: 'external_id', type: 'string', length: 64, unique: true)]
        private string $externalId,
        #[ORM\Column(name: 'user_id', type: 'subscription_user_id')]
        private UserId $userId,
        #[ORM\Column(type: 'string', length: 16, enumType: Plan::class)]
        private Plan $plan,
        #[ORM\Column(type: 'string', length: 16, enumType: PaymentStatus::class)]
        private PaymentStatus $status,
        Amount $amount,
        int $durationDays,
    ) {
        $this->amountValue = $amount->getValue();
        $this->amountCurrency = $amount->getCurrency();
        $this->durationDays = $durationDays;
        $this->createdAt = new DateTimeImmutable();
    }

    public static function createPending(
        Id $id,
        ExternalId $externalId,
        UserId $userId,
        Plan $plan,
        Amount $amount,
        int $durationDays,
    ): self {
        return new self(
            $id,
            $externalId->getValue(),
            $userId,
            $plan,
            PaymentStatus::PENDING,
            $amount,
            $durationDays,
        );
    }

    public function confirm(): void
    {
        if (PaymentStatus::SUCCEEDED === $this->status) {
            return;
        }

        if (PaymentStatus::PENDING !== $this->status) {
            throw new DomainException('Only pending payment can be confirmed.');
        }

        $this->status = PaymentStatus::SUCCEEDED;
        $this->confirmedAt = new DateTimeImmutable();

        $this->recordEvent(new PaymentConfirmed(
            $this->id,
            $this->userId,
            $this->plan,
            $this->durationDays,
        ));
    }

    public function fail(): void
    {
        if (PaymentStatus::SUCCEEDED === $this->status) {
            throw new DomainException('Succeeded payment cannot be marked as failed.');
        }

        if (PaymentStatus::FAILED === $this->status) {
            return;
        }

        $this->status = PaymentStatus::FAILED;
    }

    public function getId(): Id
    {
        return $this->id;
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function getExternalId(): ExternalId
    {
        return new ExternalId($this->externalId);
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getPlan(): Plan
    {
        return $this->plan;
    }

    public function getStatus(): PaymentStatus
    {
        return $this->status;
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function getAmount(): Amount
    {
        return new Amount($this->amountValue, $this->amountCurrency);
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function getDurationDays(): int
    {
        return $this->durationDays;
    }

    /** @psalm-suppress PossiblyUnusedMethod */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getConfirmedAt(): ?DateTimeImmutable
    {
        return $this->confirmedAt;
    }
}
