<?php

namespace App\Subscriber;

use App\Exception\ApiException;
use App\Response\ApiResponse;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\KernelInterface;

readonly class KernelExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(private KernelInterface $kernel)
    {
    }
    public function onKernelException(ExceptionEvent $event): void
    {
        $isDevEnvironment = $this->kernel->getEnvironment() === "dev";
        $exception = $event->getThrowable();
        if ($exception instanceof ApiException) {
            if ($isDevEnvironment) {
                return;
            } else {
                $response = new ApiResponse(
                    json_encode(["errors" => $exception->getErrors()]),
                    $exception->getStatusCode(),
                    [
                        "Content-Type" => "application/json"
                    ]
                );
                $event->setResponse($response);
            }
        }
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => "onKernelException"
        ];
    }
}
