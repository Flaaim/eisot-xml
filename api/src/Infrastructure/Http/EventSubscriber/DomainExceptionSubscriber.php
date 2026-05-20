<?php

declare(strict_types=1);

namespace Infrastructure\Http\EventSubscriber;

use DomainException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class DomainExceptionSubscriber implements EventSubscriberInterface
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof DomainException) {
            return;
        }
        $response = new JsonResponse([
            'message' => $exception->getMessage(),
        ], JsonResponse::HTTP_BAD_REQUEST);

        $event->setResponse($response);
    }
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 10],
        ];
    }
}
