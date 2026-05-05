<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CustomersRestApi\Api\Storefront\Provider;

use ApiPlatform\Metadata\Post;
use Generated\Api\Storefront\CustomersAddressesStorefrontResource;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\ApiPlatform\State\Provider\AbstractStorefrontProvider;
use Spryker\Client\Customer\CustomerClientInterface;
use Spryker\Glue\CustomersRestApi\Api\Storefront\Exception\CustomersExceptionFactory;
use Spryker\Glue\CustomersRestApi\Api\Storefront\Mapper\CustomersAddressesResourceMapperInterface;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Symfony\Component\HttpFoundation\Response;

class CustomersAddressesStorefrontProvider extends AbstractStorefrontProvider
{
    public function __construct(
        protected CustomerClientInterface $customerClient,
        protected SerializerServiceInterface $serializer,
        protected CustomersExceptionFactory $exceptionFactory,
        protected CustomersAddressesResourceMapperInterface $customersAddressesResourceMapper,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     *
     * @return array<object>
     */
    protected function provideCollection(): array
    {
        if (!$this->hasCustomer()) {
            throw $this->exceptionFactory->createAddressNotFoundException(Response::HTTP_UNAUTHORIZED);
        }

        $customerReference = $this->getUriVariables()['customerReference'] ?? null;

        if ($customerReference === null) {
            return [];
        }

        $customerTransfer = (new CustomerTransfer())->setCustomerReference($customerReference);

        $customerResponse = $this->customerClient->findCustomerByReference($customerTransfer);

        if (!$customerResponse->getIsSuccess() || !$customerResponse->getCustomerTransfer()) {
            return [];
        }

        $customerTransfer = $customerResponse->getCustomerTransfer();
        $addressesTransfer = $customerTransfer->getAddresses();

        if ($addressesTransfer === null) {
            return [];
        }

        $resources = [];

        foreach ($this->customersAddressesResourceMapper->mapAddressesTransferToResourceDataArray($addressesTransfer, $customerTransfer) as $resourceData) {
            $resourceData['customerReference'] = $customerReference;
            $resources[] = $this->serializer->denormalize($resourceData, CustomersAddressesStorefrontResource::class);
        }

        return $resources;
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function provideItem(): ?object
    {
        // POST creates a new resource — no existing item to load.
        if ($this->getOperation() instanceof Post) {
            return null;
        }

        $request = $this->getRequest();
        $customerReference = $this->getUriVariables()['customerReference'] ?? null;
        $uuid = $this->getUriVariables()['uuid'] ?? $request->attributes->get('uuid');

        if ($customerReference === null || $uuid === null) {
            throw $this->exceptionFactory->createAddressNotFoundException();
        }

        $customerTransfer = (new CustomerTransfer())->setCustomerReference($customerReference);

        $customerResponse = $this->customerClient->findCustomerByReference($customerTransfer);

        if (!$customerResponse->getIsSuccess() || !$customerResponse->getCustomerTransfer()) {
            throw $this->exceptionFactory->createAddressNotFoundException();
        }

        $customerTransfer = $customerResponse->getCustomerTransfer();
        $addressesTransfer = $customerTransfer->getAddresses();

        if ($addressesTransfer === null) {
            throw $this->exceptionFactory->createAddressNotFoundException();
        }

        foreach ($addressesTransfer->getAddresses() as $addressTransfer) {
            if ($addressTransfer->getUuid() !== $uuid) {
                continue;
            }

            $resourceData = $this->customersAddressesResourceMapper->mapAddressTransferToResourceData($addressTransfer, $customerTransfer);
            $resourceData['customerReference'] = $customerReference;

            return $this->serializer->denormalize($resourceData, CustomersAddressesStorefrontResource::class);
        }

        throw $this->exceptionFactory->createAddressNotFoundException();
    }
}
