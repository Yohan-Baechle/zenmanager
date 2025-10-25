<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::RESPONSE, priority: 0)]
class SecurityHeadersListener
{
    public function __invoke(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $request = $event->getRequest();
        $response = $event->getResponse();
        $headers = $response->headers;

        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('X-Frame-Options', 'DENY');
        $headers->set('X-XSS-Protection', '1; mode=block');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        if (str_starts_with($request->getPathInfo(), '/api/doc')) {
            $headers->set(
                'Content-Security-Policy',
                "default-src 'self'; ".
                "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net; ".
                "style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; ".
                "img-src 'self' data: https://cdn.jsdelivr.net; ".
                "font-src 'self' data: https://cdn.jsdelivr.net; ".
                "connect-src 'self'; ".
                "frame-ancestors 'none'; ".
                "base-uri 'self'; ".
                "form-action 'self'"
            );
        } else {
            $headers->set(
                'Content-Security-Policy',
                "default-src 'self'; ".
                "script-src 'self'; ".
                "style-src 'self' 'unsafe-inline'; ".
                "img-src 'self' data:; ".
                "font-src 'self'; ".
                "connect-src 'self'; ".
                "frame-ancestors 'none'; ".
                "base-uri 'self'; ".
                "form-action 'self'"
            );
        }

        // TODO - Uncomment in production with HTTPS
        // $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
    }
}
