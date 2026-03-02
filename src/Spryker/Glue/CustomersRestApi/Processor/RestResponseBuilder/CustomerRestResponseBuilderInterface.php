<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CustomersRestApi\Processor\RestResponseBuilder;

use ArrayObject;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\RestCustomersResponseAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface CustomerRestResponseBuilderInterface
{
    public function createCustomerRestResource(
        string $customerUuid,
        RestCustomersResponseAttributesTransfer $restCustomersResponseAttributesTransfer,
        ?CustomerTransfer $customerTransfer = null
    ): RestResourceInterface;

    public function createNoContentResponse(): RestResponseInterface;

    /**
     * @param \ArrayObject<int, \Generated\Shared\Transfer\CustomerErrorTransfer> $customerErrorTransfers
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function createCustomerConfirmationErrorResponse(ArrayObject $customerErrorTransfers): RestResponseInterface;

    public function createCustomerConfirmationCodeMissingErrorResponse(): RestResponseInterface;
}
