<?php

declare(strict_types=1);

namespace Tests\Functional\Company\ChangeCompanyInn;

use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\Inn;
use App\Company\Entity\Company\Name;
use App\Company\Test\Builder\CompanyBuilder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    public const COMPANY_ID  = '6a7cd1a1-4cf0-4946-94bc-1997eaca2078';
    public const COMPANY_INN = '7707083893';

    /** ИНН другой компании — уже занят в базе */
    public const INN_EXISTS  = '5009053535';

    public function load(ObjectManager $manager): void
    {
        // Компания, у которой будем менять ИНН
        $company = (new CompanyBuilder())
            ->withId(new Id(self::COMPANY_ID))
            ->withInn(Inn::fromString(self::COMPANY_INN))
            ->build();

        $manager->persist($company);

        // Вторая компания с занятым ИНН
        $other = (new CompanyBuilder())
            ->withName(Name::fromString('Другая компания'))
            ->withInn(Inn::fromString(self::INN_EXISTS))
            ->build();

        $manager->persist($other);
        $manager->flush();
    }
}
