<?php

declare(strict_types=1);

namespace Tests\Functional\Company\ChangeCompanyInn;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id as UserId;
use App\Auth\Test\Builder\UserBuilder;
use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\Inn;
use App\Company\Entity\Company\Name;
use App\Company\Entity\Company\UserId as CompanyUserId;
use App\Company\Test\Builder\CompanyBuilder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    public const string COMPANY_ID  = '6a7cd1a1-4cf0-4946-94bc-1997eaca2078';
    public const string COMPANY_INN = '7707083893';
    /** ИНН другой компании — уже занят в базе */
    public const string INN_EXISTS  = '5009053535';
    public const string USER_ID     = 'd4e5f6a7-b8c9-4d0e-8f2a-b3c4d5e6f7a8';
    public const string USER_EMAIL  = 'inn-owner@test.com';
    public const string USER_PASS   = 'password';
    /** Другой пользователь — не владелец компании */
    public const string OTHER_USER_ID    = 'e5f6a7b8-c9d0-4e1f-8a3b-c4d5e6f7a8b9';
    public const string OTHER_USER_EMAIL = 'inn-other@test.com';

    public function load(ObjectManager $manager): void
    {
        // Владелец компании
        $owner = (new UserBuilder())
            ->withId(new UserId(self::USER_ID))
            ->withEmail(new Email(self::USER_EMAIL))
            ->withPassword(self::USER_PASS)
            ->active()
            ->build();
        $manager->persist($owner);

        // Другой пользователь
        $other = (new UserBuilder())
            ->withId(new UserId(self::OTHER_USER_ID))
            ->withEmail(new Email(self::OTHER_USER_EMAIL))
            ->withPassword(self::USER_PASS)
            ->active()
            ->build();
        $manager->persist($other);

        // Компания, у которой будем менять ИНН
        $company = (new CompanyBuilder())
            ->withId(new Id(self::COMPANY_ID))
            ->withInn(Inn::fromString(self::COMPANY_INN))
            ->withUserId(new CompanyUserId(self::USER_ID))
            ->build();
        $manager->persist($company);

        // Вторая компания с занятым ИНН
        $other2 = (new CompanyBuilder())
            ->withName(Name::fromString('Другая компания'))
            ->withInn(Inn::fromString(self::INN_EXISTS))
            ->withUserId(new CompanyUserId(self::USER_ID))
            ->build();
        $manager->persist($other2);

        $manager->flush();
    }
}
