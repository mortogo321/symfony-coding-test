<?php

namespace App\EventListener;

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

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();

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

        $response = new JsonResponse([
            'status' => 'error',
            'message' => $exception->getMessage(),
            'errors' => $errors ?: null,
        ], $statusCode);

        $event->setResponse($response);
    }
}
