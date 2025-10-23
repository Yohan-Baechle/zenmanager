<?php

namespace App\EventListener;

use Doctrine\DBAL\Exception\ConnectionException;
use Doctrine\DBAL\Exception\DriverException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityNotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener(event: 'kernel.exception')]
class ExceptionListener
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly string $environment
    ) {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $correlationId = uniqid('err_', true);

        $this->logException($exception, $request, $correlationId);

        $response = $this->createApiResponse($exception, $correlationId);
        $event->setResponse($response);
    }

    private function logException(\Throwable $exception, $request, string $correlationId): void
    {
        $statusCode = $this->getStatusCode($exception);
        $context = [
            'correlation_id' => $correlationId,
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'file' => $exception->getFile(),
            'line' => $exception->getLine(),
            'method' => $request->getMethod(),
            'uri' => $request->getRequestUri(),
            'ip' => $request->getClientIp(),
        ];

        if ($statusCode >= 500) {
            $this->logger->error('API Exception: ' . $exception->getMessage(), $context);
        } elseif ($statusCode >= 400) {
            $this->logger->warning('API Client Error: ' . $exception->getMessage(), $context);
        } else {
            $this->logger->info('API Exception: ' . $exception->getMessage(), $context);
        }
    }

    private function createApiResponse(\Throwable $exception, string $correlationId): JsonResponse
    {
        $statusCode = $this->getStatusCode($exception);
        $data = $this->getErrorData($exception, $statusCode, $correlationId);

        return new JsonResponse($data, $statusCode);
    }

    private function getStatusCode(\Throwable $exception): int
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        if ($exception instanceof ValidationFailedException) {
            return Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        if ($exception instanceof EntityNotFoundException) {
            return Response::HTTP_NOT_FOUND;
        }

        if ($exception instanceof UniqueConstraintViolationException) {
            return Response::HTTP_CONFLICT;
        }

        if ($exception instanceof ConnectionException || $exception instanceof DriverException) {
            return Response::HTTP_SERVICE_UNAVAILABLE;
        }

        return Response::HTTP_INTERNAL_SERVER_ERROR;
    }

    private function getErrorData(\Throwable $exception, int $statusCode, string $correlationId): array
    {
        $data = [
            'error' => $this->getErrorType($statusCode),
            'message' => $this->getErrorMessage($exception, $statusCode),
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
            'correlation_id' => $correlationId,
        ];

        if ($exception instanceof ValidationFailedException) {
            $data['errors'] = $this->formatValidationErrors($exception);
        } elseif ($exception instanceof UnprocessableEntityHttpException && $exception->getPrevious() instanceof ValidationFailedException) {
            $data['errors'] = $this->formatValidationErrors($exception->getPrevious());
        }

        $isDev = $this->environment === 'dev';
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
            $statusCode === 409 => 'Conflict',
            $statusCode === 422 => 'Validation Error',
            $statusCode === 429 => 'Too Many Requests',
            $statusCode === 503 => 'Service Unavailable',
            $statusCode >= 500 => 'Internal Server Error',
            default => 'Error'
        };
    }

    private function getErrorMessage(\Throwable $exception, int $statusCode): string
    {
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

        if ($exception instanceof UniqueConstraintViolationException) {
            return 'A resource with this value already exists';
        }

        if ($exception instanceof ConnectionException) {
            return 'Database connection failed';
        }

        if ($exception instanceof DriverException) {
            return 'Database error occurred';
        }

        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getMessage() ?: 'An error occurred';
        }

        $isDev = $this->environment === 'dev';
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
