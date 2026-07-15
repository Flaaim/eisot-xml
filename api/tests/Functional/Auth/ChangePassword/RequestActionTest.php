<?php

declare(strict_types=1);

namespace Tests\Functional\Auth\ChangePassword;

use App\Auth\Entity\User\Id;
use App\Auth\Entity\User\UserRepository;
use App\OAuth\Entity\UserAdapter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Tests\Functional\FixturesLoader;
use Tests\Functional\Json;
use Tests\Functional\OAuthTokenTrait;

/**
 * @internal
 * @coversNothing
 */
final class RequestActionTest extends WebTestCase
{
    use OAuthTokenTrait;
    private readonly KernelBrowser $client;
    private readonly UserRepository $users;
    private readonly UserAdapter $userAdapter;
    private string $ownerToken;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $container = $this->client->getContainer();

        $fixtures = new FixturesLoader($container);
        $fixtures->loadFixtures([RequestFixture::class]);

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $this->users = new UserRepository($em);

        $this->ownerToken = $this->getAccessToken($this->client, RequestFixture::EMAIL, RequestFixture::PASSWORD);
    }

    public function testUnauthenticatedReturns401(): void
    {
        $this->client->request('PUT', '/v1/auth/user/password/change', [
            'currentPassword' => 'hashedPassword',
            'newPassword' => 'newPassword',
        ]);
        self::assertEquals(401, $this->client->getResponse()->getStatusCode());
    }

    public function testOldPasswordNotFound(): void
    {
        $userAdapter = new UserAdapter(RequestFixture::JOIN_BY_GOOGLE['userId']);

        $this->client->loginUser($userAdapter, 'api');

        $this->client->jsonRequest('PUT', '/v1/auth/user/password/change', [
            'currentPassword' => 'hashedPassword',
            'newPassword' => 'newPassword',
        ]);

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'User does not have an old password.'], $data);
    }

    public function testIncorrectCurrentPassword(): void
    {
        $this->client->jsonRequest(
            'PUT',
            '/v1/auth/user/password/change',
            [
                'currentPassword' => 'Incorrect',
                'newPassword' => 'newPassword',
            ],
            $this->authHeaders($this->ownerToken)
        );

        self::assertEquals(409, $this->client->getResponse()->getStatusCode());

        self::assertJson($body = $this->client->getResponse()->getContent());
        $data = Json::decode($body);

        self::assertEquals(['message' => 'Incorrect current password.'], $data);
    }

    public function testSuccess(): void
    {
        $this->client->jsonRequest(
            'PUT',
            '/v1/auth/user/password/change',
            [
                'currentPassword' => RequestFixture::PASSWORD,
                'newPassword' => 'newPassword',
            ],
            $this->authHeaders($this->ownerToken)
        );

        self::assertEquals(204, $this->client->getResponse()->getStatusCode());

        $user = $this->users->get(new Id(RequestFixture::OWNER_ID));

        self::assertTrue(password_verify('newPassword', $user->getPasswordHash()));
    }
}
