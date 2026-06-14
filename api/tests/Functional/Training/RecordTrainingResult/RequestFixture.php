<?php

declare(strict_types=1);

namespace Tests\Functional\Training\RecordTrainingResult;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id as UserId;
use App\Auth\Test\Builder\UserBuilder;
use App\Company\Entity\Company\Id as CompanyId;
use App\Company\Entity\Company\Inn;
use App\Company\Entity\Company\UserId as CompanyUserId;
use App\Company\Test\Builder\CompanyBuilder;
use App\Worker\Entity\Worker\CompanyId as WorkerCompanyId;
use App\Worker\Entity\Worker\FullName;
use App\Worker\Entity\Worker\Profession;
use App\Worker\Entity\Worker\Snils;
use App\Worker\Entity\Worker\SnilsInfo;
use App\Worker\Entity\Worker\Worker;
use App\Worker\Entity\Worker\WorkerId;
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

        // Компания
        $company = (new CompanyBuilder())
            ->withId(new CompanyId(self::COMPANY_ID))
            ->withInn(Inn::fromString('7707083893'))
            ->withUserId(new CompanyUserId(self::USER_ID))
            ->build();
        $manager->persist($company);

        // Работник
        $worker = Worker::register(
            new WorkerId(self::WORKER_ID),
            new WorkerCompanyId(self::COMPANY_ID),
            FullName::create('Иванов', 'Иван', 'Иванович'),
            Profession::fromString('Слесарь'),
            SnilsInfo::forCitizen(Snils::fromString('644-670-185 07')),
        );
        $manager->persist($worker);

        $manager->flush();
    }
}
