<?php

declare(strict_types=1);

namespace Tests\Functional\Subscription\SubscriptionPurchased;

use App\Auth\Entity\User\Id;
use App\Auth\Test\Builder\UserBuilder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    public const string USER_ID = 'c415fe70-be45-41a2-b84a-67313bff73c8';

    public function load(ObjectManager $manager): void
    {
        $user = new UserBuilder()
            ->withId(new Id(self::USER_ID))
            ->build();
        $manager->persist($user);

        $manager->flush();
    }
}
