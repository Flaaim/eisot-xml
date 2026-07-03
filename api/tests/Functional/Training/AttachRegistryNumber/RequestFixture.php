<?php

declare(strict_types=1);

namespace Tests\Functional\Training\AttachRegistryNumber;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id as UserId;
use App\Auth\Test\Builder\UserBuilder;
use App\Company\Entity\Company\Id as CompanyId;
use App\Company\Entity\Company\Inn;
use App\Company\Entity\Company\UserId as CompanyUserId;
use App\Company\Test\Builder\CompanyBuilder;
use App\Training\Entity\Record\Id as RecordId;
use App\Training\Entity\Record\Program;
use App\Training\Entity\Record\ProtocolNumber;
use App\Training\Entity\Record\Result;
use App\Training\Entity\Record\TrainingRecord;
use App\Training\Entity\Record\WorkerId as TrainingWorkerId;
use App\Worker\Entity\Worker\CompanyId as WorkerCompanyId;
use App\Worker\Entity\Worker\FullName;
use App\Worker\Entity\Worker\Profession;
use App\Worker\Entity\Worker\Snils;
use App\Worker\Entity\Worker\SnilsInfo;
use App\Worker\Entity\Worker\Worker;
use App\Worker\Entity\Worker\WorkerId;
use DateTimeImmutable;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    public const string COMPANY_ID  = '30678b36-8c68-4a1a-8305-e843f0820f9f';
    public const string WORKER_ID   = '5a6b7c8d-9e0f-4a1b-8c2d-3e4f5a6b7c8d';
    public const string RECORD_ID   = '6a7b8c9d-0e1f-4a2b-8c3d-4e5f6a7b8c9d';
    public const string USER_ID     = 'c1d2e3f4-a5b6-4c7d-8e9f-a0b1c2d3e4f5';
    public const string USER_EMAIL  = 'attach-owner@test.com';
    public const string USER_PASS   = 'password';

    public function load(ObjectManager $manager): void
    {
        $owner = (new UserBuilder())
            ->withId(new UserId(self::USER_ID))
            ->withEmail(new Email(self::USER_EMAIL))
            ->withPassword(self::USER_PASS)
            ->active()
            ->build();
        $manager->persist($owner);

        $company = (new CompanyBuilder())
            ->withId(new CompanyId(self::COMPANY_ID))
            ->withInn(Inn::fromString('7707083893'))
            ->withUserId(new CompanyUserId(self::USER_ID))
            ->build();
        $manager->persist($company);

        $worker = Worker::register(
            new WorkerId(self::WORKER_ID),
            new WorkerCompanyId(self::COMPANY_ID),
            FullName::create('Петров', 'Пётр'),
            Profession::fromString('Электрик'),
            SnilsInfo::forCitizen(Snils::fromString('112-233-445 95')),
        );
        $manager->persist($worker);

        $record = TrainingRecord::record(
            new RecordId(self::RECORD_ID),
            new TrainingWorkerId(self::WORKER_ID),
            Program::fromId(1),
            Result::satisfactory(),
            new DateTimeImmutable('2023-09-28 16:56:01'),
            ProtocolNumber::fromString('ПР-001/2023'),
        );
        $manager->persist($record);

        $manager->flush();
    }
}
