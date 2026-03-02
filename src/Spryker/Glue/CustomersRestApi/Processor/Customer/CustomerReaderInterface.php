<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CustomersRestApi\Processor\Customer;

use Generated\Shared\Transfer\CustomerResponseTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

interface CustomerReaderInterface
{
    public function getCustomerByCustomerReference(RestRequestInterface $restRequest): RestResponseInterface;

    public function findCustomer(RestRequestInterface $restRequest): CustomerResponseTransfer;

    public function getCurrentCustomer(RestRequestInterface $restRequest): CustomerResponseTransfer;
}
