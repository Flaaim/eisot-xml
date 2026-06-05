<?php

declare(strict_types=1);

namespace Tests\Functional\Company\AddCompany;

use App\Company\Test\Builder\CompanyBuilder;
use App\Company\Entity\Company\Inn;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    public const INN_EXISTS = '7707083893';

    public function load(ObjectManager $manager): void
    {
        $company = (new CompanyBuilder())
            ->withInn(Inn::fromString(self::INN_EXISTS))
            ->build();

        $manager->persist($company);
        $manager->flush();
    }
}
