<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CustomersRestApi\Api\Storefront\EventSubscriber;

use Spryker\ApiPlatform\Exception\GlueApiException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Catches empty-segment (double-slash) URLs for Customer-module resources
 * before the generic JsonApiRequestValidatorSubscriber (priority 33).
 *
 * Returns error code 411 instead of the generic 802 for customer-owned paths.
 */
class CustomerRequestValidatorSubscriber implements EventSubscriberInterface
{
    protected const string ERROR_CODE_UNAUTHORIZED = '411';

    protected const string ERROR_DETAIL_UNAUTHORIZED_REQUEST = 'Unauthorized request.';

    protected const string ERROR_DETAIL_RESOURCE_ID_NOT_SPECIFIED = 'Resource id is not specified.';

    protected const string RESOURCE_SEGMENT_ADDRESSES = 'addresses';

    protected const string PATH_PREFIX_CUSTOMERS = 'customers';

    protected const string PATH_PREFIX_CUSTOMER_DASH = 'customer-';

    /**
     * @return array<string, array<int, array{string, int}>>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['onKernelRequestEmptySegment', 34],
            ],
        ];
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    public function onKernelRequestEmptySegment(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $pathInfo = $event->getRequest()->getPathInfo();

        if (!str_contains($pathInfo, '//')) {
            return;
        }

        if (!$this->isCustomerModulePath($pathInfo)) {
            return;
        }

        $method = $event->getRequest()->getMethod();

        if (in_array($method, ['PATCH', 'DELETE'], true)) {
            throw new GlueApiException(
                Response::HTTP_BAD_REQUEST,
                '',
                static::ERROR_DETAIL_RESOURCE_ID_NOT_SPECIFIED,
            );
        }

        throw new GlueApiException(
            Response::HTTP_FORBIDDEN,
            static::ERROR_CODE_UNAUTHORIZED,
            static::ERROR_DETAIL_UNAUTHORIZED_REQUEST,
        );
    }

    protected function isCustomerModulePath(string $pathInfo): bool
    {
        $segments = explode('/', trim($pathInfo, '/'));
        $firstSegment = $segments[0];

        // /customer-password/{ref}, /customer-forgotten-password, etc.
        if (str_starts_with($firstSegment, static::PATH_PREFIX_CUSTOMER_DASH)) {
            return true;
        }

        if ($firstSegment !== static::PATH_PREFIX_CUSTOMERS) {
            return false;
        }

        // /customers or /customers/{ref} (direct customer resource)
        if (count($segments) <= 2) {
            return true;
        }

        // /customers/{ref}/addresses (customer sub-resource)
        return ($segments[2] ?? '') === static::RESOURCE_SEGMENT_ADDRESSES;
    }
}
