<?php

declare(strict_types=1);

namespace App\Auth\Test\Unit\Entity\User;

use App\Auth\Entity\User\Network;
use App\Auth\Test\Builder\UserBuilder;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 * @coversNothing
 */
final class NetworkTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = new UserBuilder()->build();
        $network = new Network($user, $name = 'google', $identity = 'google-1');

        self::assertEquals($name, $network->getNetwork());
        self::assertEquals($identity, $network->getIdentity());
    }

    public function testEmptyName(): void
    {
        $user = new UserBuilder()->build();
        $this->expectException(InvalidArgumentException::class);
        new Network($user, $name = '', $identity = 'google-1');
    }

    public function testEmptyIdentity(): void
    {
        $user = new UserBuilder()->build();
        $this->expectException(InvalidArgumentException::class);
        new Network($user, $name = 'google', $identity = '');
    }

    public function testEqual(): void
    {
        $user = new UserBuilder()->build();
        $network = new Network($user, $name = 'google', $identity = 'google-1');

        self::assertTrue($network->isEqualTo(new Network($user, $name, 'google-1')));
        self::assertFalse($network->isEqualTo(new Network($user, $name, 'google-2')));
        self::assertFalse($network->isEqualTo(new Network($user, 'vk', 'vk-1')));
    }
}
