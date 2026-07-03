<?php

declare(strict_types=1);

namespace App\Auth\Test\Unit\Entity\User\Command\JoinByEmail;

use App\Auth\Entity\User\Token;
use App\Auth\Event\UserJoinConfirmed;
use App\Auth\Test\Builder\UserBuilder;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

/**
 * @internal
 * @coversNothing
 */
final class ConfirmTest extends TestCase
{
    public function testSuccess(): void
    {
        $user = new UserBuilder()
            ->withJoinConfirmToken($token = $this->createToken())
            ->build();

        self::assertTrue($user->isWait());
        self::assertFalse($user->isActive());

        $expiresAt = $token->getExpiresAt();
        self::assertNotNull($expiresAt);
        $user->confirmJoin(
            $token->getValue(),
            $expiresAt->modify('-1 day')
        );

        self::assertFalse($user->isWait());
        self::assertTrue($user->isActive());

        self::assertNull($user->getJoinConfirmToken());

        self::assertNotEmpty($events = $user->releaseEvents());

        $event = end($events);

        self::assertInstanceOf(UserJoinConfirmed::class, $event);
        self::assertEquals($user->getEmail()->getValue(), $event->email);
    }

    public function testWrong(): void
    {
        $user = new UserBuilder()
            ->withJoinConfirmToken($token = $this->createToken())
            ->build();

        $this->expectExceptionMessage('Token is invalid.');

        $expiresAt = $token->getExpiresAt();
        self::assertNotNull($expiresAt);
        $user->confirmJoin(
            Uuid::uuid4()->toString(),
            $expiresAt->modify('-1 day')
        );
    }

    public function testExpired(): void
    {
        $user = new UserBuilder()
            ->withJoinConfirmToken($token = $this->createToken())
            ->build();

        $this->expectExceptionMessage('Token is expired.');

        $expiresAt = $token->getExpiresAt();
        self::assertNotNull($expiresAt);
        $user->confirmJoin(
            $token->getValue(),
            $expiresAt->modify('+1 day')
        );
    }

    public function testAlready(): void
    {
        $token = $this->createToken();

        $user = new UserBuilder()
            ->withJoinConfirmToken($token)
            ->active()
            ->build();

        $this->expectExceptionMessage('Confirmation is not required.');

        $expiresAt = $token->getExpiresAt();
        self::assertNotNull($expiresAt);
        $user->confirmJoin(
            $token->getValue(),
            $expiresAt->modify('-1 day')
        );
    }

    private function createToken(): Token
    {
        return new Token(
            Uuid::uuid4()->toString(),
            new DateTimeImmutable('+1 day')
        );
    }
}
