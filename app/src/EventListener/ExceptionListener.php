<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Serializer\Exception\MissingConstructorArgumentsException;
use DomainException;
use ErrorException;
use InvalidArgumentException;
use LogicException;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        $response = match (true) {
            $exception instanceof InvalidArgumentException,
            $exception instanceof ErrorException,
            $exception instanceof DomainException,
            $exception instanceof LogicException,
            $exception instanceof FileException,
            $exception instanceof MissingConstructorArgumentsException => $this->generateResponse(
                $exception->getMessage(),
                Response::HTTP_BAD_REQUEST
            ),
            $exception instanceof AccessDeniedException => $this->generateResponse(
                'Access Denied',
                Response::HTTP_FORBIDDEN
            ),
            $exception instanceof NotFoundHttpException => $this->generateResponse(
                'Route not found',
                Response::HTTP_NOT_FOUND
            ),
            $exception instanceof MethodNotAllowedHttpException => $this->generateResponse(
                $exception->getMessage(),
                Response::HTTP_METHOD_NOT_ALLOWED
            ),
            default => $this->generateResponse(
                $exception->getMessage(),
                Response::HTTP_INTERNAL_SERVER_ERROR
            ),
        };

        $event->setResponse($response);
    }

    private function generateResponse(string $message, int $status): JsonResponse
    {
        return new JsonResponse(['error' => $message], $status);
    }
}
