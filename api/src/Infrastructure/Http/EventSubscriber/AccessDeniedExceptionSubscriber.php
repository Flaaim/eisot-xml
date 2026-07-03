<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\EventSubscriber;

use App\Company\Exception\AccessDeniedException as CompanyAccessDenied;
use App\Training\Exception\AccessDeniedException as TrainingAccessDenied;
use App\Worker\Exception\AccessDeniedException as WorkerAccessDenied;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Перехватывает AccessDeniedException и возвращает HTTP 403 Forbidden.
 *
 * Выполняется с более высоким приоритетом (20), чем DomainExceptionSubscriber (10),
 * чтобы AccessDeniedException не был захвачен как обычный DomainException.
 */
final class AccessDeniedExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly LoggerInterface $logger
    ) {}

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof CompanyAccessDenied && !$exception instanceof WorkerAccessDenied && !$exception instanceof TrainingAccessDenied) {
            return;
        }

        $this->logger->warning($exception->getMessage(), [
            'exception' => $exception,
            'url'       => $event->getRequest()->getUri(),
        ]);

        $response = new JsonResponse([
            'message' => $exception->getMessage(),
        ], JsonResponse::HTTP_FORBIDDEN);

        $event->setResponse($response);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 20],
        ];
    }
}
