<?php

declare(strict_types=1);

namespace App\OAuth\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use League\OAuth2\Server\Entities\Traits\RefreshTokenTrait;

/**
 * @psalm-suppress MissingConstructor
 * @psalm-suppress ClassMustBeFinal
 */
#[ORM\Entity]
#[ORM\Table(name: 'oauth_refresh_tokens')]
class RefreshToken implements RefreshTokenEntityInterface
{
    use EntityTrait;
    use RefreshTokenTrait;

    /**
     * @var non-empty-string
     */
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 80)]
    protected string $identifier;

    #[ORM\Column(type: 'datetime_immutable')]
    protected DateTimeImmutable $expiryDateTime;
    /**
     * @var non-empty-string|null
     */
    #[ORM\Column(type: 'guid', nullable: false)]
    private ?string $userIdentifier = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $revoked = false;

    public function setAccessToken(AccessTokenEntityInterface $accessToken): void
    {
        $this->accessToken = $accessToken;
        $identifier = (string)$accessToken->getUserIdentifier();
        if($identifier === ''){
            throw new InvalidArgumentException('Access token cannot be empty string');
        }
        $this->userIdentifier = $identifier;
    }

    public function getUserIdentifier(): ?string
    {
        /** @var non-empty-string|null */
        return $this->userIdentifier;
    }

    public function revoked(): void
    {
        if(!$this->isRevoked()){
            $this->revoked = true;
        }
    }

    public function isRevoked(): bool
    {
        return $this->revoked;
    }
}
