<?php

declare(strict_types=1);

namespace Tests\Functional\Company\GetCompany;

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
    public const string USER_ID      = 'a1b2c3d4-e5f6-4a7b-8c9d-e0f1a2b3c4d5';
    public const string USER_EMAIL   = 'owner@test.com';
    public const string USER_PASS    = 'password';

    public const string OTHER_USER_EMAIL = 'other@test.com';
    public const string OTHER_USER_ID    = 'b2c3d4e5-f6a7-4b8c-9d0e-f1a2b3c4d5e6';

    public const string COMPANY_ID  = '6a7cd1a1-4cf0-4946-94bc-1997eaca2078';
    public const string COMPANY_NAME = 'ООО Название';
    public const string COMPANY_INN  = '7707083893';

    public const string COMPANY_NOT_FOUND_ID  = 'c87916aa-2edf-4eb8-9dda-37b711bdb17f';

    public function load(ObjectManager $manager): void
    {
        $owner = new UserBuilder()
            ->withId(new UserId(self::USER_ID))
            ->withEmail(new Email(self::USER_EMAIL))
            ->withPassword(self::USER_PASS)
            ->active()
            ->build();
        $manager->persist($owner);

        $other = new UserBuilder()
            ->withId(new UserId(self::OTHER_USER_ID))
            ->withEmail(new Email(self::OTHER_USER_EMAIL))
            ->withPassword(self::USER_PASS)
            ->active()
            ->build();
        $manager->persist($other);

        $company = new CompanyBuilder()
            ->withId(new Id(self::COMPANY_ID))
            ->withName(Name::fromString(self::COMPANY_NAME))
            ->withInn(Inn::fromString(self::COMPANY_INN))
            ->withUserId(new CompanyUserId(self::USER_ID))
            ->build();
        $manager->persist($company);

        $manager->flush();
    }
}
