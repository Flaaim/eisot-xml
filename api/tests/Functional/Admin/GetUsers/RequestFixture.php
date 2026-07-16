<?php

declare(strict_types=1);

namespace Tests\Functional\Admin\GetUsers;

use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\Id as UserId;
use App\Auth\Entity\User\Role;
use App\Auth\Test\Builder\UserBuilder;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Persistence\ObjectManager;

final class RequestFixture extends AbstractFixture
{
    public const string ADMIN_ID = 'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa';
    public const string ADMIN_EMAIL = 'admin-panel@test.com';
    public const string USER_ID = 'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb';
    public const string USER_EMAIL = 'regular-user@test.com';
    public const string USER_PASS = 'password';

    public function load(ObjectManager $manager): void
    {
        $admin = (new UserBuilder())
            ->withId(new UserId(self::ADMIN_ID))
            ->withEmail(new Email(self::ADMIN_EMAIL))
            ->withPassword(self::USER_PASS)
            ->withRole(Role::admin())
            ->active()
            ->build();
        $manager->persist($admin);

        $user = (new UserBuilder())
            ->withId(new UserId(self::USER_ID))
            ->withEmail(new Email(self::USER_EMAIL))
            ->withPassword(self::USER_PASS)
            ->active()
            ->build();
        $manager->persist($user);

        $manager->flush();
    }
}
