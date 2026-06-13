<?php

declare(strict_types=1);

namespace Tests\Functional\Company\ArchiveCompany;

use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\Inn;
use App\Company\Test\Builder\CompanyBuilder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    /** Активная компания, которую будем архивировать */
    public const string COMPANY_ID  = '30678b36-8c68-4a1a-8305-e843f0820f9f';
    public const string COMPANY_INN = '7707083893';

    public function load(ObjectManager $manager): void
    {
        $company = new CompanyBuilder()
            ->withId(new Id(self::COMPANY_ID))
            ->withInn(Inn::fromString(self::COMPANY_INN))
            ->build();

        $manager->persist($company);
        $manager->flush();
    }
}
