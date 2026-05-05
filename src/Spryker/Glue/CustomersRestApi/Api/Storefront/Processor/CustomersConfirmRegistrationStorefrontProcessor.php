<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CustomersRestApi\Api\Storefront\Processor;

use Generated\Api\Storefront\CustomersConfirmRegistrationStorefrontResource;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractStorefrontProcessor;
use Spryker\Client\Customer\CustomerClientInterface;
use Spryker\Glue\CustomersRestApi\Api\Storefront\Exception\CustomersExceptionFactory;
use Spryker\Glue\CustomersRestApi\CustomersRestApiConfig;

class CustomersConfirmRegistrationStorefrontProcessor extends AbstractStorefrontProcessor
{
    public function __construct(
        protected CustomerClientInterface $customerClient,
        protected CustomersExceptionFactory $exceptionFactory,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processPost(mixed $data): mixed
    {
        $customerTransfer = (new CustomerTransfer())
            ->setRegistrationKey($data->getRegistrationKey());

        $customerResponseTransfer = $this->customerClient->confirmCustomerRegistration($customerTransfer);

        if (!$customerResponseTransfer->getIsSuccess()) {
            throw $this->exceptionFactory->createExceptionFromCustomerResponse(
                $customerResponseTransfer,
                CustomersRestApiConfig::RESPONSE_CODE_CONFIRMATION_FAILED,
                CustomersRestApiConfig::RESPONSE_MESSAGE_CONFIRMATION_FAILED,
            );
        }

        return $this->mapCustomerTransferToResource($customerResponseTransfer->getCustomerTransfer());
    }

    protected function mapCustomerTransferToResource(CustomerTransfer $customerTransfer): CustomersConfirmRegistrationStorefrontResource
    {
        $resource = new CustomersConfirmRegistrationStorefrontResource();

        $resource->setCustomerReference($customerTransfer->getCustomerReference());
        $resource->setEmail($customerTransfer->getEmail());
        $resource->setFirstName($customerTransfer->getFirstName());
        $resource->setLastName($customerTransfer->getLastName());
        $resource->setSalutation($customerTransfer->getSalutation());
        $resource->setGender($customerTransfer->getGender());
        $resource->setDateOfBirth($customerTransfer->getDateOfBirth());
        $resource->setCreatedAt($customerTransfer->getCreatedAt());
        $resource->setUpdatedAt($customerTransfer->getUpdatedAt());

        return $resource;
    }
}
