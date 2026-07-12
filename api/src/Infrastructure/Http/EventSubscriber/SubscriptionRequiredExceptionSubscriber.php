<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\EventSubscriber;

use App\Subscription\Exception\CompanyLimitReachedException;
use App\Subscription\Exception\SubscriptionRequiredException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Возвращает HTTP 403 при отсутствии подписки или превышении лимита компаний.
 *
 * @psalm-suppress UnusedClass
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

        if ($exception instanceof SubscriptionRequiredException) {
            $event->setResponse(new JsonResponse([
                'message' => $exception->getMessage(),
                'code' => 'subscription_required',
            ], JsonResponse::HTTP_FORBIDDEN));

            return;
        }

        if ($exception instanceof CompanyLimitReachedException) {
            $event->setResponse(new JsonResponse([
                'message' => $exception->getMessage(),
                'code' => 'company_limit_reached',
            ], JsonResponse::HTTP_FORBIDDEN));
        }
    }
}
