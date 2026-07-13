<?php

declare(strict_types=1);

namespace Tests\Functional\Training\RemoveTrainingRecord;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id as UserId;
use App\Auth\Test\Builder\UserBuilder;
use App\Company\Entity\Company\Id as CompanyId;
use App\Company\Entity\Company\Inn;
use App\Company\Entity\Company\UserId as CompanyUserId;
use App\Company\Test\Builder\CompanyBuilder;
use App\Training\Entity\Record\Id as RecordId;
use App\Training\Entity\Record\WorkerId;
use App\Training\Test\Builder\TrainingRecordBuilder;
use App\Worker\Entity\Worker\CompanyId as WorkerCompanyId;
use App\Worker\Entity\Worker\FullName;
use App\Worker\Entity\Worker\Profession;
use App\Worker\Entity\Worker\Snils;
use App\Worker\Entity\Worker\SnilsInfo;
use App\Worker\Entity\Worker\Worker;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    public const string COMPANY_ID   = '20678b36-8c68-4a1a-8305-e843f0820f9f';
    public const string WORKER_ID    = '4a5b6c7d-8e9f-4a0b-8c1d-2e3f4a5b6c7d';
    public const string USER_ID      = 'a1b2c3d4-e5f6-4a7b-8c9d-e0f1a2b3c4d5';
    public const string USER_EMAIL   = 'training-owner@test.com';
    public const string USER_PASS    = 'password';
    public const string OTHER_USER_ID    = 'b2c3d4e5-f6a7-4b8c-9d0e-f1a2b3c4d5e6';
    public const string OTHER_USER_EMAIL = 'training-other@test.com';

    public const string RECORD_ID_ONE = '388881ab-a0e3-4269-9d30-eb7e68844f1a';
    public const string RECORD_ID_TWO = '7da54dcc-8aec-4a7a-890a-35556581045b';

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
            ->withId(new CompanyId(self::COMPANY_ID))
            ->withInn(Inn::fromString('7707083893'))
            ->withUserId(new CompanyUserId(self::USER_ID))
            ->build();
        $manager->persist($company);

        $worker = Worker::register(
            new \App\Worker\Entity\Worker\WorkerId(self::WORKER_ID),
            new WorkerCompanyId(self::COMPANY_ID),
            FullName::create('Иванов', 'Иван', 'Иванович'),
            Profession::fromString('Слесарь'),
            SnilsInfo::forCitizen(Snils::fromString('112-233-445 95')),
        );
        $manager->persist($worker);

        $record1 = new TrainingRecordBuilder()
            ->withId(new RecordId(self::RECORD_ID_ONE))
            ->withWorkerId(new WorkerId(self::WORKER_ID))
            ->build();
        $manager->persist($record1);

        $record2 = new TrainingRecordBuilder()
            ->withId(new RecordId(self::RECORD_ID_TWO))
            ->withWorkerId(new WorkerId(self::WORKER_ID))
            ->build();
        $manager->persist($record2);

        $manager->flush();
    }
}
