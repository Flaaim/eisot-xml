<?php

declare(strict_types=1);

namespace Tests\Functional\Company\AddCompany;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id as UserId;
use App\Auth\Test\Builder\UserBuilder;
use App\Company\Entity\Company\Inn;
use App\Company\Entity\Company\UserId as CompanyUserId;
use App\Company\Test\Builder\CompanyBuilder;
use App\Subscription\Entity\Subscription\Id as SubscriptionId;
use App\Subscription\Entity\Subscription\Period;
use App\Subscription\Entity\Subscription\Plan;
use App\Subscription\Entity\Subscription\Subscription;
use App\Subscription\Entity\Subscription\UserId as SubscriptionUserId;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    public const string INN_EXISTS  = '7707083893';
    public const string USER_ID     = 'c3d4e5f6-a7b8-4c9d-8e1f-a2b3c4d5e6f7';
    public const string USER_EMAIL  = 'company-owner@test.com';
    public const string USER_PASS   = 'password';

    public function load(ObjectManager $manager): void
    {
        // Создаём активного пользователя — владельца компании
        $user = (new UserBuilder())
            ->withId(new UserId(self::USER_ID))
            ->withEmail(new Email(self::USER_EMAIL))
            ->withPassword(self::USER_PASS)
            ->active()
            ->build();
        $manager->persist($user);

        // Компания с существующим ИНН для теста дублирования
        $company = (new CompanyBuilder())
            ->withInn(Inn::fromString(self::INN_EXISTS))
            ->withUserId(new CompanyUserId(self::USER_ID))
            ->build();
        $manager->persist($company);

        // Extended Plan — пользователь уже имеет 1 компанию, для добавления второй нужен безлимитный тариф
        $subscription = Subscription::activate(
            SubscriptionId::generate(),
            new SubscriptionUserId(self::USER_ID),
            Plan::EXTENDED,
            Period::fromDurationDays(30),
        );
        $manager->persist($subscription);

        $manager->flush();
    }
}
