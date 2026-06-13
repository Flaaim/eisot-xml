<?php

declare(strict_types=1);

namespace Tests\Functional\Company\ArchiveCompany;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id as UserId;
use App\Auth\Test\Builder\UserBuilder;
use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\Inn;
use App\Company\Entity\Company\UserId as CompanyUserId;
use App\Company\Test\Builder\CompanyBuilder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    /** Активная компания, которую будем архивировать */
    public const string COMPANY_ID   = '30678b36-8c68-4a1a-8305-e843f0820f9f';
    public const string COMPANY_INN  = '7707083893';
    public const string USER_ID      = 'f6a7b8c9-d0e1-4f2a-8b4c-d5e6f7a8b9c0';
    public const string USER_EMAIL   = 'archive-owner@test.com';
    public const string USER_PASS    = 'password';
    /** Другой пользователь — не владелец компании */
    public const string OTHER_USER_ID    = 'a7b8c9d0-e1f2-4a3b-8c5d-e6f7a8b9c0d1';
    public const string OTHER_USER_EMAIL = 'archive-other@test.com';

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

        // Активная компания, принадлежащая владельцу
        $company = (new CompanyBuilder())
            ->withId(new Id(self::COMPANY_ID))
            ->withInn(Inn::fromString(self::COMPANY_INN))
            ->withUserId(new CompanyUserId(self::USER_ID))
            ->build();

        $manager->persist($company);
        $manager->flush();
    }
}
