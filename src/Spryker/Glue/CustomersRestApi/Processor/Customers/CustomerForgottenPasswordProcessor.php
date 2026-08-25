<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CustomersRestApi\Processor\Customers;

use Generated\Shared\Transfer\RestCustomerForgottenPasswordAttributesTransfer;
use Spryker\Glue\CustomersRestApi\Dependency\Client\CustomersRestApiToCustomerClientInterface;
use Spryker\Glue\CustomersRestApi\Processor\Mapper\CustomerForgottenPasswordResourceMapperInterface;
use Spryker\Glue\CustomersRestApi\Processor\RestResponseBuilder\CustomerRestResponseBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

class CustomerForgottenPasswordProcessor implements CustomerForgottenPasswordProcessorInterface
{
    public function __construct(
        protected CustomersRestApiToCustomerClientInterface $customerClient,
        protected CustomerForgottenPasswordResourceMapperInterface $customerForgottenPasswordResourceMapper,
        protected CustomerRestResponseBuilderInterface $customerRestResponseBuilder,
    ) {
    }

    public function sendPasswordRestoreMail(
        RestCustomerForgottenPasswordAttributesTransfer $restCustomerForgottenPasswordAttributesTransfer
    ): RestResponseInterface {
        $customerTransfer = $this->customerForgottenPasswordResourceMapper
            ->mapCustomerForgottenPasswordAttributesToCustomerTransfer($restCustomerForgottenPasswordAttributesTransfer);
        $customerResponseTransfer = $this->customerClient->sendPasswordRestoreMail($customerTransfer);

        if (!$customerResponseTransfer->getIsSuccess()) {
            return $this->customerRestResponseBuilder->createEmailNotSentErrorResponse();
        }

        return $this->customerRestResponseBuilder->createNoContentResponse();
    }
}
