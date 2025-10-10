<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\RateLimiter\LimiterInterface;

#[AsEventListener(event: 'kernel.response')]
class RateLimitHeaderListener
{
    public function __invoke(ResponseEvent $event): void
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        // Only add headers to API routes
        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        // Get rate limit info from request attributes (set by RateLimiterListener)
        $limit = $request->attributes->get('rate_limit');

        if ($limit instanceof \Symfony\Component\RateLimiter\Limit) {
            $response->headers->set('X-RateLimit-Limit', (string) $limit->getLimit());
            $response->headers->set('X-RateLimit-Remaining', (string) $limit->getRemainingTokens());

            if ($limit->getRetryAfter()) {
                $response->headers->set('X-RateLimit-Reset', (string) $limit->getRetryAfter()->getTimestamp());
            }
        }
    }
}
