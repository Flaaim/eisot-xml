<?php

declare(strict_types=1);

namespace Tests\Functional\Company\RenameCompany;

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
    public const string COMPANY_NAME = 'ООО Старое Название';
    public const string COMPANY_INN  = '7707083893';
    public const string USER_ID      = 'a1b2c3d4-e5f6-4a7b-8c9d-e0f1a2b3c4d5';
    public const string USER_EMAIL   = 'rename-owner@test.com';
    public const string USER_PASS    = 'password';
    /** Другой пользователь — не владелец компании */
    public const string OTHER_USER_ID    = 'b2c3d4e5-f6a7-4b8c-9d0e-f1a2b3c4d5e6';
    public const string OTHER_USER_EMAIL = 'rename-other@test.com';

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

        // Компания, принадлежащая владельцу
        $company = (new CompanyBuilder())
            ->withId(new Id(self::COMPANY_ID))
            ->withName(Name::fromString(self::COMPANY_NAME))
            ->withInn(Inn::fromString(self::COMPANY_INN))
            ->withUserId(new CompanyUserId(self::USER_ID))
            ->build();

        $manager->persist($company);
        $manager->flush();
    }
}
