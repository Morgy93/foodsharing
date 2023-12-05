<?php

namespace Foodsharing\RestApi;

use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Symfony\Component\RateLimiter\RateLimiterFactory;

/**
 * General class that contains common functions for all REST controllers.
 */
class FoodsharingRestController extends AbstractFOSRestController
{
    /**
     * Checks if the request violates the specified rate limiting and throws an exception if it does. If
     * the rate limiting is not violated, the function will not do anything.
     *
     * @throws TooManyRequestsHttpException if the limit is reached
     */
    protected function checkRateLimit(Request $request, RateLimiterFactory $rateLimiter): void
    {
        $limiter = $rateLimiter->create($request->getClientIp());
        if (!$limiter->consume()->isAccepted()) {
            throw new TooManyRequestsHttpException();
        }
    }
}
