<?php

declare(strict_types=1);

namespace Tests\Functional\Subscription\PaymentWebhook;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id as UserId;
use App\Auth\Test\Builder\UserBuilder;
use App\Subscription\Entity\Payment\Amount;
use App\Subscription\Entity\Payment\ExternalId;
use App\Subscription\Entity\Payment\Id;
use App\Subscription\Entity\Payment\Payment;
use App\Subscription\Entity\Subscription\Plan;
use App\Subscription\Entity\Subscription\UserId as SubscriptionUserId;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    public const string USER_ID = 'cccccccc-cccc-4ccc-8ccc-cccccccccccc';
    public const string USER_EMAIL = 'payment-webhook@test.com';
    public const string USER_PASS = 'password';
    public const string PAYMENT_ID = 'dddddddd-dddd-4ddd-8ddd-dddddddddddd';
    public const string EXTERNAL_ID = 'test-yookassa-webhook-payment';

    public function load(ObjectManager $manager): void
    {
        $user = (new UserBuilder())
            ->withId(new UserId(self::USER_ID))
            ->withEmail(new Email(self::USER_EMAIL))
            ->withPassword(self::USER_PASS)
            ->active()
            ->build();
        $manager->persist($user);

        $payment = Payment::createPending(
            new Id(self::PAYMENT_ID),
            new ExternalId(self::EXTERNAL_ID),
            new SubscriptionUserId(self::USER_ID),
            Plan::BASIC,
            Amount::fromRubles('490.00'),
            30,
        );
        $manager->persist($payment);

        $manager->flush();
    }
}
