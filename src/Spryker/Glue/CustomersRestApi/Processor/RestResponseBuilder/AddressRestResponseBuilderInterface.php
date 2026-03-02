<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CustomersRestApi\Processor\RestResponseBuilder;

use Generated\Shared\Transfer\RestAddressAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface AddressRestResponseBuilderInterface
{
    public function createRestResponse(): RestResponseInterface;

    public function createAddressRestResource(
        string $addressUuid,
        string $customerReference,
        RestAddressAttributesTransfer $restAddressAttributesTransfer
    ): RestResourceInterface;
}
