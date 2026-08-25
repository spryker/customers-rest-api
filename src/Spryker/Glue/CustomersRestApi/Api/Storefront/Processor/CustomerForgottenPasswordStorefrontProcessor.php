<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CustomersRestApi\Api\Storefront\Processor;

use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractStorefrontProcessor;
use Spryker\Client\Customer\CustomerClientInterface;
use Spryker\Glue\CustomersRestApi\Api\Storefront\Exception\CustomersExceptionFactory;
use Spryker\Glue\CustomersRestApi\CustomersRestApiConfig;

class CustomerForgottenPasswordStorefrontProcessor extends AbstractStorefrontProcessor
{
    public function __construct(
        protected CustomerClientInterface $customerClient,
        protected CustomersExceptionFactory $customersExceptionFactory,
    ) {
    }

    protected function processPost(mixed $data): mixed
    {
        $customerTransfer = (new CustomerTransfer())
            ->setEmail($data->getEmail());

        $customerResponseTransfer = $this->customerClient->sendPasswordRestoreMail($customerTransfer);

        if (!$customerResponseTransfer->getIsSuccess()) {
            throw $this->customersExceptionFactory->createExceptionFromCustomerResponse(
                $customerResponseTransfer,
                CustomersRestApiConfig::RESPONSE_CODE_FAILED_TO_SEND_EMAIL,
                CustomersRestApiConfig::RESPONSE_MESSAGE_FAILED_TO_SEND_EMAIL,
            );
        }

        return null;
    }
}
