<?php

declare(strict_types=1);

namespace Tests\Functional\Company\RemoveCompany;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id as UserId;
use App\Auth\Test\Builder\UserBuilder;
use App\Company\Entity\Company\Id;
use App\Company\Entity\Company\Inn;
use App\Company\Entity\Company\UserId as CompanyUserId;
use App\Company\Test\Builder\CompanyBuilder;
use App\Training\Test\Builder\TrainingRecordBuilder;
use App\Worker\Entity\Worker\CompanyId as WorkerCompanyId;
use App\Worker\Entity\Worker\WorkerId;
use App\Worker\Test\Builder\WorkerBuilder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    public const string ARCHIVED_COMPANY_ID = '40678b36-8c68-4a1a-8305-e843f0820f9f';
    public const string ACTIVE_COMPANY_ID   = '50678b36-8c68-4a1a-8305-e843f0820f9f';
    public const string WORKER_ID           = '60678b36-8c68-4a1a-8305-e843f0820f9f';
    public const string USER_ID             = 'f6a7b8c9-d0e1-4f2a-8b4c-d5e6f7a8b9c0';
    public const string USER_EMAIL          = 'remove-owner@test.com';
    public const string USER_PASS           = 'password';
    public const string OTHER_USER_ID       = 'a7b8c9d0-e1f2-4a3b-8c5d-e6f7a8b9c0d1';
    public const string OTHER_USER_EMAIL    = 'remove-other@test.com';

    public function load(ObjectManager $manager): void
    {
        $owner = (new UserBuilder())
            ->withId(new UserId(self::USER_ID))
            ->withEmail(new Email(self::USER_EMAIL))
            ->withPassword(self::USER_PASS)
            ->active()
            ->build();
        $manager->persist($owner);

        $other = (new UserBuilder())
            ->withId(new UserId(self::OTHER_USER_ID))
            ->withEmail(new Email(self::OTHER_USER_EMAIL))
            ->withPassword(self::USER_PASS)
            ->active()
            ->build();
        $manager->persist($other);

        $archivedCompany = (new CompanyBuilder())
            ->withId(new Id(self::ARCHIVED_COMPANY_ID))
            ->withInn(Inn::fromString('500100732259'))
            ->withUserId(new CompanyUserId(self::USER_ID))
            ->build();
        $archivedCompany->archive();
        $manager->persist($archivedCompany);

        $activeCompany = (new CompanyBuilder())
            ->withId(new Id(self::ACTIVE_COMPANY_ID))
            ->withInn(Inn::fromString('7707083893'))
            ->withUserId(new CompanyUserId(self::USER_ID))
            ->build();
        $manager->persist($activeCompany);

        $worker = (new WorkerBuilder())
            ->withId(new WorkerId(self::WORKER_ID))
            ->withCompanyId(new WorkerCompanyId(self::ARCHIVED_COMPANY_ID))
            ->build();
        $manager->persist($worker);

        $trainingRecord = (new TrainingRecordBuilder())
            ->withWorkerId(new \App\Training\Entity\Record\WorkerId(self::WORKER_ID))
            ->build();
        $manager->persist($trainingRecord);

        $manager->flush();
    }
}
