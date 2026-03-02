<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CustomersRestApi\Processor\Address;

use Generated\Shared\Transfer\AddressTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

interface AddressReaderInterface
{
    public function getAddressesByAddressUuid(RestRequestInterface $restRequest): RestResponseInterface;

    public function findAddressByUuid(RestRequestInterface $restRequest, string $uuid): ?AddressTransfer;
}
