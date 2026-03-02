<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CustomersRestApi\Processor\Mapper;

use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\RestCustomersAttributesTransfer;
use Generated\Shared\Transfer\RestCustomersResponseAttributesTransfer;

interface CustomerResourceMapperInterface
{
    public function mapCustomerAttributesToCustomerTransfer(RestCustomersAttributesTransfer $restCustomersAttributesTransfer): CustomerTransfer;

    public function mapCustomerTransferToRestCustomersResponseAttributesTransfer(
        CustomerTransfer $customerTransfer,
        RestCustomersResponseAttributesTransfer $restCustomersResponseAttributesTransfer
    ): RestCustomersResponseAttributesTransfer;
}
