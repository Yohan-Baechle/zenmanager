<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\RateLimiter\RateLimiterFactory;

#[AsEventListener(event: 'kernel.request', priority: 10)]
class RateLimiterListener
{
    public function __construct(
        private readonly RateLimiterFactory $loginLimiterLimiter,
        private readonly RateLimiterFactory $apiLimiterLimiter,
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        if (str_starts_with($request->getPathInfo(), '/api/doc')) {
            return;
        }

        $identifier = $request->getClientIp() ?? 'unknown';

        if (str_starts_with($request->getPathInfo(), '/api/login')) {
            $limiter = $this->loginLimiterLimiter->create($identifier);
            $limit = $limiter->consume(1);

            if (!$limit->isAccepted()) {
                $event->setResponse(new JsonResponse([
                    'error' => 'Too Many Requests',
                    'message' => 'Too many login attempts. Please try again later.',
                    'retryAfter' => $limit->getRetryAfter()->getTimestamp(),
                ], Response::HTTP_TOO_MANY_REQUESTS, [
                    'X-RateLimit-Remaining' => $limit->getRemainingTokens(),
                    'X-RateLimit-Retry-After' => $limit->getRetryAfter()->getTimestamp(),
                    'X-RateLimit-Limit' => $limit->getLimit(),
                ]));

                return;
            }

            $request->attributes->set('rate_limit', $limit);

            return;
        }

        $limiter = $this->apiLimiterLimiter->create($identifier);
        $limit = $limiter->consume(1);

        if (!$limit->isAccepted()) {
            $event->setResponse(new JsonResponse([
                'error' => 'Too Many Requests',
                'message' => 'Rate limit exceeded. Please slow down.',
                'retryAfter' => $limit->getRetryAfter()->getTimestamp(),
            ], Response::HTTP_TOO_MANY_REQUESTS, [
                'X-RateLimit-Remaining' => $limit->getRemainingTokens(),
                'X-RateLimit-Retry-After' => $limit->getRetryAfter()->getTimestamp(),
                'X-RateLimit-Limit' => $limit->getLimit(),
            ]));

            return;
        }

        $request->attributes->set('rate_limit', $limit);
    }
}
