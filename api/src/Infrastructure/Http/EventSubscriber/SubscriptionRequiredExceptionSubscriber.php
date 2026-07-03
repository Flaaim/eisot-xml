<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\EventSubscriber;

use App\Subscription\Exception\SubscriptionRequiredException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Возвращает HTTP 403 при попытке сформировать RegistrySet XML без подписки.
 */
final class SubscriptionRequiredExceptionSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 20],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof SubscriptionRequiredException) {
            return;
        }

        $response = new JsonResponse([
            'message' => $exception->getMessage(),
            'code' => 'subscription_required',
        ], JsonResponse::HTTP_FORBIDDEN);

        $event->setResponse($response);
    }
}
