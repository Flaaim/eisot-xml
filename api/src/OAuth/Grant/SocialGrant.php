<?php

declare(strict_types=1);

namespace App\OAuth\Grant;

use App\Auth\Command\JoinByNetwork\Command;
use App\Auth\Command\JoinByNetwork\Handler;
use App\Auth\Command\AttachNetwork\Command as AttachNetworkCommand;
use App\Auth\Command\AttachNetwork\Handler as AttachNetworkHandler;
use App\Auth\Entity\User\Email;
use App\Auth\Entity\User\UserRepository;
use App\Infrastructure\Social\Registry\ClientRegistryInterface;
use DateInterval;
use DomainException;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Grant\AbstractGrant;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use League\OAuth2\Server\ResponseTypes\ResponseTypeInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class SocialGrant extends AbstractGrant
{
    /**
     * @psalm-suppress PossiblyUnusedMethod
     */
    public function __construct(
        private readonly ClientRegistryInterface $registry,
        private readonly Handler $joinHandler,
        private readonly UserRepository $domainUserRepository,
        private readonly LoggerInterface $logger,
        private readonly AttachNetworkHandler $attachNetworkHandler,
        RefreshTokenRepositoryInterface $refreshTokenRepository,
    ) {
        $this->setRefreshTokenRepository($refreshTokenRepository);
        $this->refreshTokenTTL = new DateInterval('P1M');
    }

    public function getIdentifier(): string
    {
        return 'social';
    }

    public function respondToAccessTokenRequest(
        ServerRequestInterface $request,
        ResponseTypeInterface $responseType,
        DateInterval $accessTokenTTL
    ): ResponseTypeInterface {
        $client = $this->validateClient($request);

        $parsedBody = $request->getParsedBody();
        $network = $parsedBody['network'] ?? null;
        $code = $parsedBody['code'] ?? null;
        $redirectUri = $parsedBody['redirect_uri'] ?? null;

        if (null === $network || null === $code || null === $redirectUri) {
            throw OAuthServerException::invalidRequest('network or code');
        }

        try {
            $socialUser = $this->registry->create($code, $network, $redirectUri);
            if (null === $socialUser->email) {
                throw OAuthServerException::invalidRequest('email not provided');
            }
        } catch (Throwable $e) {
            $this->logger->error('Social Auth Error (Provider fetch): {message}', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
            throw OAuthServerException::serverError('Ошибка авторизации через соцсеть: ' . $e->getMessage());
        }
        try {
            $localUser = $this->domainUserRepository->findByNetwork($socialUser->network, $socialUser->identity);
            if (!$localUser) {
                $existingEmailUser = $this->domainUserRepository->findByEmail(new Email($socialUser->email));
                if ($existingEmailUser) {
                    $command = new AttachNetworkCommand(
                        $existingEmailUser->getEmail()->getValue(),
                        $socialUser->network,
                        $socialUser->identity,
                    );
                    $this->attachNetworkHandler->handle($command);
                }else{
                    $command = new Command($socialUser->email, $socialUser->network, $socialUser->identity);
                    $this->joinHandler->handle($command);
                }

                $localUser = $this->domainUserRepository->findByNetwork($socialUser->network, $socialUser->identity);
            }
        } catch (DomainException $e) {
            throw OAuthServerException::invalidGrant($e->getMessage());
        } catch (Throwable $e) {
            $this->logger->error('Social Auth Error (Database/Register):', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
            throw OAuthServerException::serverError('Ошибка при регистрации через соцсеть: ' . $e->getMessage());
        }

        if (!$localUser) {
            throw OAuthServerException::serverError('Не удалось получить пользователя после регистрации.');
        }

        try {
            $scopes = $this->validateScopes($this->getRequestParameter('scope', $request, $this->defaultScope));

            $userIdentifier = $localUser->getId()->getValue();

            $accessToken = $this->issueAccessToken($accessTokenTTL, $client, $userIdentifier, $scopes);

            $refreshToken = $this->issueRefreshToken($accessToken);
            if (null === $refreshToken) {
                throw OAuthServerException::invalidRefreshToken('Refresh token not found');
            }
            $responseType->setAccessToken($accessToken);
            $responseType->setRefreshToken($refreshToken);

            return $responseType;
        } catch (Throwable $e) {
            $this->logger->error('Social Auth Error (Token Generation):', [
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
            throw OAuthServerException::serverError('Ошибка на этапе генерации токенов: ' . $e->getMessage());
        }
    }
}
