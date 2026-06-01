<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Auth\RevokeToken;

use App\OAuth\Command\RevokeToken\Handler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/v1/auth/token/revoke', name: 'auth.token.revoke', methods: ['POST'])]
final class RequestAction
{
    public function __construct(
        private readonly Handler $handler
    ) {}

    public function __invoke(Request $request): Response
    {
        $data = json_decode($request->getContent(), true);

        $token = \is_array($data) ? ($data['token'] ?? null) : null;

        if (null === $token) {
            return new Response(null, Response::HTTP_OK);
        }

        $this->handler->handle($token);

        return new Response(null, Response::HTTP_OK);
    }
}
