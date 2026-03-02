<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CustomersRestApi\Processor\Activator;

use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\Glue\CustomersRestApi\Dependency\Client\CustomersRestApiToCustomerClientInterface;
use Spryker\Glue\CustomersRestApi\Processor\RestResponseBuilder\CustomerRestResponseBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

class CustomerActivator implements CustomerActivatorInterface
{
    /**
     * @var \Spryker\Glue\CustomersRestApi\Dependency\Client\CustomersRestApiToCustomerClientInterface
     */
    protected $customerClient;

    /**
     * @var \Spryker\Glue\CustomersRestApi\Processor\RestResponseBuilder\CustomerRestResponseBuilderInterface
     */
    protected $customerRestResponseBuilder;

    public function __construct(
        CustomersRestApiToCustomerClientInterface $customerClient,
        CustomerRestResponseBuilderInterface $customerRestResponseBuilder
    ) {
        $this->customerClient = $customerClient;
        $this->customerRestResponseBuilder = $customerRestResponseBuilder;
    }

    public function confirmCustomer(RestRequestInterface $restRequest): RestResponseInterface
    {
        /** @var \Generated\Shared\Transfer\RestCustomerConfirmationAttributesTransfer $restCustomerConfirmationAttributesTransfer */
        $restCustomerConfirmationAttributesTransfer = $restRequest->getResource()->getAttributes();

        if (!$restCustomerConfirmationAttributesTransfer->getRegistrationKey()) {
            return $this->customerRestResponseBuilder->createCustomerConfirmationCodeMissingErrorResponse();
        }

        $customerTransfer = (new CustomerTransfer())
            ->setRegistrationKey($restCustomerConfirmationAttributesTransfer->getRegistrationKey());

        $customerResponseTransfer = $this->customerClient->confirmCustomerRegistration($customerTransfer);

        if (!$customerResponseTransfer->getIsSuccess()) {
            return $this->customerRestResponseBuilder
                ->createCustomerConfirmationErrorResponse($customerResponseTransfer->getErrors());
        }

        return $this->customerRestResponseBuilder->createNoContentResponse();
    }
}
