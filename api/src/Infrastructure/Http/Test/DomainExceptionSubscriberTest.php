<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Test;

use DomainException;
use Infrastructure\Http\EventSubscriber\DomainExceptionSubscriber;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;


final class DomainExceptionSubscriberTest extends TestCase
{


    public function testGetSubscribedEvents(): void
    {
        $subscriber = DomainExceptionSubscriber::getSubscribedEvents();
        self::assertArrayHasKey(KernelEvents::EXCEPTION, $subscriber);
        self::assertEquals(['onKernelException', 10], $subscriber['kernel.exception']);
    }

    public function testProcessDomainException(): void
    {
        $exception = new DomainException('DomainException');
        $subscriber = new DomainExceptionSubscriber();

        $request = new Request();
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );


        $subscriber->onKernelException($event);
        $response = $event->getResponse();

        self::assertNotNull($response);
        self::assertInstanceOf(JsonResponse::class, $response);
        self::assertEquals(JsonResponse::HTTP_BAD_REQUEST, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        self::assertEquals(['message' => $exception->getMessage()], $data);
    }
    public function testProcessException(): void
    {
        $exception = new RuntimeException('RuntimeException');
        $subscriber = new DomainExceptionSubscriber();

        $request = new Request();
        $kernel = $this->createMock(HttpKernelInterface::class);
        $event = new ExceptionEvent(
            $kernel,
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            $exception
        );

        $subscriber->onKernelException($event);
        $response = $event->getResponse();

        self::assertNull($response);
    }
}
