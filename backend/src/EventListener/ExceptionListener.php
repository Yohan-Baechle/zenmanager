<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Doctrine\ORM\EntityNotFoundException;

#[AsEventListener(event: 'kernel.exception')]
class ExceptionListener
{

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        // Only handle API requests (starting with /api)
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $response = $this->createApiResponse($exception);
        $event->setResponse($response);
    }

    private function createApiResponse(\Throwable $exception): JsonResponse
    {
        $statusCode = $this->getStatusCode($exception);
        $data = $this->getErrorData($exception, $statusCode);

        return new JsonResponse($data, $statusCode);
    }

    private function getStatusCode(\Throwable $exception): int
    {
        // HTTP exceptions
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        // Validation errors
        if ($exception instanceof ValidationFailedException) {
            return Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Entity not found
        if ($exception instanceof EntityNotFoundException) {
            return Response::HTTP_NOT_FOUND;
        }

        // Default to 500 for unexpected errors
        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    private function getErrorData(\Throwable $exception, int $statusCode): array
    {
        $data = [
            'error' => $this->getErrorType($statusCode),
            'message' => $this->getErrorMessage($exception, $statusCode),
        ];

        // Add validation errors if present
        if ($exception instanceof ValidationFailedException) {
            $data['errors'] = $this->formatValidationErrors($exception);
        } elseif ($exception instanceof UnprocessableEntityHttpException && $exception->getPrevious() instanceof ValidationFailedException) {
            $data['errors'] = $this->formatValidationErrors($exception->getPrevious());
        }

        // Add exception details in dev environment
        $isDev = ($_ENV['APP_ENV'] ?? 'prod') === 'dev';
        if ($isDev && $statusCode >= 500) {
            $data['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => explode("\n", $exception->getTraceAsString())
            ];
        }

        return $data;
    }

    private function getErrorType(int $statusCode): string
    {
        return match (true) {
            $statusCode === 400 => 'Bad Request',
            $statusCode === 401 => 'Unauthorized',
            $statusCode === 403 => 'Forbidden',
            $statusCode === 404 => 'Not Found',
            $statusCode === 422 => 'Validation Error',
            $statusCode === 429 => 'Too Many Requests',
            $statusCode >= 500 => 'Internal Server Error',
            default => 'Error'
        };
    }

    private function getErrorMessage(\Throwable $exception, int $statusCode): string
    {
        // For specific exceptions, use their message
        if ($exception instanceof NotFoundHttpException) {
            return $exception->getMessage() ?: 'Resource not found';
        }

        if ($exception instanceof AccessDeniedHttpException) {
            return 'Access denied';
        }

        if ($exception instanceof UnauthorizedHttpException) {
            return 'Authentication required';
        }

        if ($exception instanceof BadRequestHttpException) {
            return $exception->getMessage() ?: 'Bad request';
        }

        if ($exception instanceof ValidationFailedException) {
            return 'Validation failed';
        }

        if ($exception instanceof EntityNotFoundException) {
            return 'Resource not found';
        }

        // For HTTP exceptions, use their message
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getMessage() ?: 'An error occurred';
        }

        // For 500 errors, don't expose internal details in production
        $isDev = ($_ENV['APP_ENV'] ?? 'prod') === 'dev';
        if ($statusCode >= 500 && !$isDev) {
            return 'An internal server error occurred';
        }

        return $exception->getMessage() ?: 'An error occurred';
    }

    private function formatValidationErrors(ValidationFailedException $exception): array
    {
        $violations = $exception->getViolations();
        $errors = [];

        foreach ($violations as $violation) {
            $propertyPath = $violation->getPropertyPath();
            $errors[$propertyPath][] = $violation->getMessage();
        }

        return $errors;
    }
}
