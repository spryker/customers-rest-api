<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CustomersRestApi\Processor\Customer;

use Generated\Shared\Transfer\RestCustomerPasswordAttributesTransfer;
use Generated\Shared\Transfer\RestCustomersAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

interface CustomerWriterInterface
{
    public function registerCustomer(
        RestRequestInterface $restRequest,
        RestCustomersAttributesTransfer $restCustomersAttributesTransfer
    ): RestResponseInterface;

    public function updateCustomerPassword(
        RestRequestInterface $restRequest,
        RestCustomerPasswordAttributesTransfer $passwordAttributesTransfer
    ): RestResponseInterface;

    public function updateCustomer(RestRequestInterface $restRequest, RestCustomersAttributesTransfer $restCustomersAttributesTransfer): RestResponseInterface;

    public function anonymizeCustomer(RestRequestInterface $restRequest): RestResponseInterface;
}
