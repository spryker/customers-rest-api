<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CustomersRestApi\Api\Storefront\EventSubscriber;

use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\ApiPlatform\Attribute\ApiType;
use Spryker\ApiPlatform\EventSubscriber\IdentityRequestSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Reads the raw OAuth identity claims stored on the request by
 * {@see \Spryker\ApiPlatform\EventSubscriber\IdentityRequestSubscriber} and builds a
 * {@see CustomerTransfer} from the customer-specific claim keys (`customer_reference`,
 * `id_customer`). Stores the resulting transfer on the request so downstream Storefront
 * Providers/Processors can consume it via
 * {@see \Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider::getCustomer()}.
 *
 * Knowledge of customer-specific claim keys lives here, not in the generic API Platform layer.
 */
#[ApiType(types: ['storefront'])]
class CustomerIdentityRequestSubscriber implements EventSubscriberInterface
{
    public const string ATTRIBUTE_CUSTOMER_TRANSFER = 'CustomerTransfer';

    public const string ATTRIBUTE_CUSTOMER_REFERENCE = 'customerReference';

    public const string ATTRIBUTE_ID_CUSTOMER = 'idCustomer';

    protected const string KEY_CUSTOMER_REFERENCE = 'customer_reference';

    protected const string KEY_ID_CUSTOMER = 'id_customer';

    protected const int PRIORITY_AFTER_IDENTITY = 6;

    /**
     * @return array<string, array{string, int}>
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', static::PRIORITY_AFTER_IDENTITY],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $claims = $request->attributes->get(IdentityRequestSubscriber::ATTRIBUTE_OAUTH_IDENTITY_CLAIMS);

        if (!is_array($claims)) {
            return;
        }

        $customerReference = isset($claims[static::KEY_CUSTOMER_REFERENCE])
            ? (string)$claims[static::KEY_CUSTOMER_REFERENCE]
            : null;

        if ($customerReference === null) {
            return;
        }

        $customerTransfer = (new CustomerTransfer())->setCustomerReference($customerReference);

        if (isset($claims[static::KEY_ID_CUSTOMER])) {
            $customerTransfer->setIdCustomer((int)$claims[static::KEY_ID_CUSTOMER]);
        }

        $request->attributes->set(static::ATTRIBUTE_CUSTOMER_TRANSFER, $customerTransfer);
        $request->attributes->set(static::ATTRIBUTE_CUSTOMER_REFERENCE, $customerTransfer->getCustomerReference());

        if ($customerTransfer->getIdCustomer() !== null) {
            $request->attributes->set(static::ATTRIBUTE_ID_CUSTOMER, $customerTransfer->getIdCustomer());
        }
    }
}
