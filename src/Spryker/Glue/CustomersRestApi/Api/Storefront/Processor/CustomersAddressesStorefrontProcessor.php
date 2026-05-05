<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CustomersRestApi\Api\Storefront\Processor;

use Generated\Api\Storefront\CustomersAddressesStorefrontResource;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractStorefrontProcessor;
use Spryker\Client\Customer\CustomerClientInterface;
use Spryker\Glue\CustomersRestApi\Api\Storefront\Exception\CustomersExceptionFactory;
use Spryker\Glue\CustomersRestApi\Api\Storefront\Mapper\CustomersAddressesResourceMapperInterface;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class CustomersAddressesStorefrontProcessor extends AbstractStorefrontProcessor
{
    protected const string MESSAGE_ADDRESS_CREATION_FAILED = 'Address creation failed';

    public function __construct(
        protected CustomerClientInterface $customerClient,
        protected SerializerServiceInterface $serializer,
        protected CustomersExceptionFactory $exceptionFactory,
        protected CustomersAddressesResourceMapperInterface $customersAddressesResourceMapper,
    ) {
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processPost(mixed $data): mixed
    {
        $customerReference = $this->getUriVariables()['customerReference'] ?? null;

        if ($customerReference === null) {
            throw $this->exceptionFactory->createAddressNotFoundException();
        }

        if (!$this->hasCustomer()) {
            throw $this->exceptionFactory->createCustomerUnauthorizedException();
        }

        $authenticatedCustomer = $this->getCustomer();

        $addressTransfer = $this->customersAddressesResourceMapper->mapResourceToAddressTransfer($data, new AddressTransfer());
        $addressTransfer->setFkCustomer($authenticatedCustomer->getIdCustomer());

        // Single Zed call — returns CustomerTransfer with all addresses (incl. the new one)
        // and recomputed default flags.
        $updatedCustomer = $this->customerClient->createAddressAndUpdateCustomerDefaultAddresses($addressTransfer);

        $createdAddress = $this->customersAddressesResourceMapper->getLastAddedAddress($updatedCustomer);

        if ($createdAddress === null) {
            throw new UnprocessableEntityHttpException(static::MESSAGE_ADDRESS_CREATION_FAILED);
        }

        $resourceData = $this->customersAddressesResourceMapper->mapAddressTransferToResourceData($createdAddress, $updatedCustomer);
        $resourceData['customerReference'] = $customerReference;

        return $this->serializer->denormalize($resourceData, CustomersAddressesStorefrontResource::class);
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processPatch(mixed $data): mixed
    {
        $request = $this->getRequest();
        $customerReference = $this->getUriVariables()['customerReference'] ?? null;
        $uuid = $this->getUriVariables()['uuid'] ?? $request->attributes->get('uuid');

        if ($customerReference === null || $uuid === null) {
            throw $this->exceptionFactory->createAddressNotFoundException();
        }

        $existingAddress = clone $this->findCustomerAddressByUuid($customerReference, $uuid);

        $updatedAddress = $this->customersAddressesResourceMapper->mapResourceToAddressTransfer($data, $existingAddress);

        // Returns CustomerTransfer with all addresses and recomputed default flags.
        // $updatedAddress already carries idCustomerAddress + uuid (from the existing address)
        // plus the merged input fields, so it serves as the post-update view of the resource.
        $updatedCustomer = $this->customerClient->updateAddressAndCustomerDefaultAddresses($updatedAddress);

        $resourceData = $this->customersAddressesResourceMapper->mapAddressTransferToResourceData($updatedAddress, $updatedCustomer);
        $resourceData['customerReference'] = $customerReference;

        return $this->serializer->denormalize($resourceData, CustomersAddressesStorefrontResource::class);
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processDelete(): mixed
    {
        $request = $this->getRequest();
        $customerReference = $this->getUriVariables()['customerReference'] ?? null;
        $uuid = $this->getUriVariables()['uuid'] ?? $request->attributes->get('uuid');

        if ($customerReference === null || $uuid === null) {
            throw $this->exceptionFactory->createAddressNotFoundException();
        }

        $addressToDelete = $this->findCustomerAddressByUuid($customerReference, $uuid);

        $this->customerClient->deleteAddress($addressToDelete);

        return null;
    }

    protected function findCustomerAddressByUuid(string $customerReference, string $uuid): AddressTransfer
    {
        $customerTransfer = (new CustomerTransfer())->setCustomerReference($customerReference);

        $customerResponse = $this->customerClient->findCustomerByReference($customerTransfer);

        if (!$customerResponse->getIsSuccess() || !$customerResponse->getCustomerTransfer()) {
            throw $this->exceptionFactory->createAddressNotFoundException();
        }

        $addressesTransfer = $customerResponse->getCustomerTransfer()->getAddresses();

        if ($addressesTransfer === null) {
            throw $this->exceptionFactory->createAddressNotFoundException();
        }

        foreach ($addressesTransfer->getAddresses() as $addressTransfer) {
            if ($addressTransfer->getUuid() === $uuid) {
                return $addressTransfer;
            }
        }

        throw $this->exceptionFactory->createAddressNotFoundException();
    }
}
