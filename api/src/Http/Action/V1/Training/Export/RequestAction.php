<?php

declare(strict_types=1);

namespace App\Http\Action\V1\Training\Export;

use App\OAuth\Entity\UserAdapter;
use App\Training\Query\ExportRegistryToXml\Handler;
use App\Training\Query\ExportRegistryToXml\Query;
use DomainException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Throwable;

#[Route('/v1/training/export', name: 'training.export', methods: ['POST'])]
final readonly class RequestAction
{
    public function __construct(
        private Handler $handler,
        private Security $security,
    ) {}

    public function __invoke(Request $request): Response
    {
        /** @var UserAdapter|null $userAdapter */
        $userAdapter = $this->security->getUser();

        if (!$userAdapter instanceof UserAdapter) {
            return new JsonResponse(['message' => 'Access Denied.'], Response::HTTP_UNAUTHORIZED);
        }

        $userId = $userAdapter->getUserIdentifier();

        try {
            $body = $request->toArray();
        } catch (Throwable) {
            return new JsonResponse(['message' => 'Invalid JSON body.'], Response::HTTP_BAD_REQUEST);
        }

        /** @var string[] $recordIds */
        $recordIds = $body['recordIds'] ?? [];

        if (empty($recordIds)) {
            return new JsonResponse(['message' => 'No records specified.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $query = new Query(recordIds: $recordIds, userId: $userId);
            $xml = $this->handler->handle($query);

            return new Response($xml, Response::HTTP_OK, [
                'Content-Type' => 'application/xml; charset=utf-8',
                'Content-Disposition' => 'attachment; filename="eisot-export.xml"',
            ]);
        } catch (DomainException $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Throwable $e) {
            return new JsonResponse(['message' => 'Internal server error: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
