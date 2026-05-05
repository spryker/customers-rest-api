<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CustomersRestApi\Api\Storefront\Provider;

use Generated\Api\Storefront\CustomersStorefrontResource;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\Customer\CustomerClientInterface;
use Spryker\Glue\CustomersRestApi\Api\Storefront\Exception\CustomersExceptionFactory;
use Spryker\Glue\CustomersRestApi\Api\Storefront\Mapper\CustomersResourceMapperInterface;
use Spryker\Glue\CustomersRestApi\CustomersRestApiConfig;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class CustomersStorefrontProvider extends AbstractStorefrontProvider
{
    /**
     * Read by {@see \Spryker\ApiPlatform\EventSubscriber\GlueApiExceptionSubscriber::resolveProviderNotFoundError()}
     * via reflection to derive the not-found error envelope returned when the resource's
     * `securityGetStatusCode` rewrites a security-denied response into a 404 (so the API
     * does not leak whether a customer reference exists for someone else's account).
     */
    public const string ERROR_CODE_CUSTOMER_NOT_FOUND = CustomersRestApiConfig::RESPONSE_CODE_CUSTOMER_NOT_FOUND;

    public const string ERROR_MESSAGE_CUSTOMER_NOT_FOUND = CustomersRestApiConfig::RESPONSE_DETAILS_CUSTOMER_NOT_FOUND;

    public function __construct(
        protected CustomerClientInterface $customerClient,
        protected SerializerServiceInterface $serializer,
        protected CustomersExceptionFactory $exceptionFactory,
        protected CustomersResourceMapperInterface $customersResourceMapper,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     *
     * @return array<\Generated\Api\Storefront\CustomersStorefrontResource>
     */
    protected function provideCollection(): array
    {
        if (!$this->hasCustomer()) {
            throw $this->exceptionFactory->createCustomerNotFoundException(Response::HTTP_UNAUTHORIZED);
        }

        $resource = $this->getCustomerByReference($this->getCustomerReference());

        if ($resource === null) {
            return [];
        }

        return [$resource];
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function provideItem(): object|null
    {
        $requestedReference = $this->getUriVariables()['customerReference'] ?? null;

        if ($requestedReference === null) {
            throw $this->exceptionFactory->createCustomerNotFoundException();
        }

        $resource = $this->getCustomerByReference($requestedReference);

        if ($resource === null) {
            throw $this->exceptionFactory->createCustomerNotFoundException();
        }

        return $resource;
    }

    protected function getCustomerByReference(string $customerReference): ?CustomersStorefrontResource
    {
        $customerTransfer = (new CustomerTransfer())->setCustomerReference($customerReference);
        $customerResponseTransfer = $this->customerClient->findCustomerByReference($customerTransfer);

        if (!$customerResponseTransfer->getHasCustomer()) {
            return null;
        }

        return $this->serializer->denormalize(
            $this->customersResourceMapper->mapCustomerTransferToResourceData($customerResponseTransfer->getCustomerTransfer()),
            CustomersStorefrontResource::class,
        );
    }
}
