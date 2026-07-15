<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Auth\ChangePassword;

use App\Auth\Command\ChangePassword\Command;
use App\Auth\Command\ChangePassword\Handler;
use App\Infrastructure\Http\Validator\Validator;
use App\OAuth\Entity\UserAdapter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class RequestAction
{
    public function __construct(
        private readonly Handler $handler,
        private readonly Validator $validator,
        private readonly Security $security,
    ) {}

    #[Route('/v1/auth/user/password/change', 'auth.user.password.change', methods: ['PUT'])]
    public function __invoke(Request $request): Response
    {
        /** @var UserAdapter|null $userAdapter */
        $userAdapter = $this->security->getUser();

        if (!$userAdapter instanceof UserAdapter) {
            return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
        }

        $body = $request->toArray();

        $currentPassword = (string)($body['currentPassword'] ?? '');
        $newPassword = (string)($body['newPassword'] ?? '');

        $command = new Command(
            id: $userAdapter->getUserIdentifier(),
            current: $currentPassword,
            new: $newPassword
        );

        $this->validator->validate($command);
        $this->handler->handle($command);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
