<?php

namespace App\EventListener;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

class ApiExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        // Only handle /api routes
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();
        $statusCode = 500;
        $errors = [];
        $message = 'An unexpected error occurred';

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();

            // Only expose exception messages for client errors (4xx).
            // Never leak internal details on 5xx.
            if ($statusCode < 500) {
                $message = $exception->getMessage();
            }

            // Check for validation errors
            $previous = $exception->getPrevious();
            if ($previous instanceof ValidationFailedException) {
                foreach ($previous->getViolations() as $violation) {
                    $errors[] = [
                        'property' => $violation->getPropertyPath(),
                        'message' => $violation->getMessage(),
                    ];
                }
            }
        }

        // Duplicate email (unique constraint) -> 409 Conflict
        if ($exception instanceof UniqueConstraintViolationException
            || ($exception->getPrevious() instanceof UniqueConstraintViolationException)
        ) {
            $statusCode = 409;
            $message = 'Email is already registered';
        }

        $response = new JsonResponse([
            'status' => 'error',
            'message' => $message,
            'errors' => $errors ?: null,
        ], $statusCode);

        $event->setResponse($response);
    }
}
