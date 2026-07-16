<?php

declare(strict_types=1);

namespace Tests\Functional\Subscription\ActivateTrialSubscription;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id as UserId;
use App\Auth\Test\Builder\UserBuilder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    public const string USER_ID = 'cccccccc-cccc-4ccc-8ccc-cccccccccccc';
    public const string USER_EMAIL = 'trial-user@test.com';
    public const string USER_PASS = 'password';

    public function load(ObjectManager $manager): void
    {
        $user = (new UserBuilder())
            ->withId(new UserId(self::USER_ID))
            ->withEmail(new Email(self::USER_EMAIL))
            ->withPassword(self::USER_PASS)
            ->active()
            ->build();
        $manager->persist($user);

        $manager->flush();
    }
}
