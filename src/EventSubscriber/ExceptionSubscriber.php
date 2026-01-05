<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private string $environment
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 0],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $statusCode = $this->getStatusCode($exception);
        $message = $this->getMessage($exception, $statusCode);

        $responseData = [
            'error' => $message,
            'code' => $statusCode
        ];

        if ($this->environment === 'dev') {
            $responseData['debug'] = [
                'exception' => $exception::class,
                'file' => $exception->getFile(),
                'line' => $exception->getLine()
            ];
        }

        $response = new JsonResponse($responseData, $statusCode);
        $event->setResponse($response);
    }

    private function getStatusCode(\Throwable $exception): int
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        if ($exception instanceof AuthenticationException) {
            return Response::HTTP_UNAUTHORIZED;
        }

        if ($exception instanceof AccessDeniedException || $exception instanceof AccessDeniedHttpException) {
            return Response::HTTP_FORBIDDEN;
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    private function getMessage(\Throwable $exception, int $statusCode): string
    {
        return match ($statusCode) {
            Response::HTTP_UNAUTHORIZED => 'Authentication required',
            Response::HTTP_FORBIDDEN => 'Access denied',
            Response::HTTP_NOT_FOUND => $exception instanceof NotFoundHttpException
                ? 'Resource not found'
                : $exception->getMessage(),
            Response::HTTP_BAD_REQUEST => $exception->getMessage() ?: 'Invalid request',
            Response::HTTP_INTERNAL_SERVER_ERROR => $this->environment === 'dev'
                ? $exception->getMessage()
                : 'Server error',
            default => $exception->getMessage() ?: 'Unknown error'
        };
    }
}
