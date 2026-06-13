<?php

declare(strict_types=1);

namespace Tests\Functional\Company\RenameCompany;

use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\Inn;
use App\Company\Entity\Company\Name;
use App\Company\Test\Builder\CompanyBuilder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    public const COMPANY_ID      = '6a7cd1a1-4cf0-4946-94bc-1997eaca2078';
    public const COMPANY_NAME    = 'ООО Старое Название';
    public const COMPANY_INN     = '7707083893';

    public function load(ObjectManager $manager): void
    {
        $company = (new CompanyBuilder())
            ->withId(new Id(self::COMPANY_ID))
            ->withName(Name::fromString(self::COMPANY_NAME))
            ->withInn(Inn::fromString(self::COMPANY_INN))
            ->build();

        $manager->persist($company);
        $manager->flush();
    }
}
