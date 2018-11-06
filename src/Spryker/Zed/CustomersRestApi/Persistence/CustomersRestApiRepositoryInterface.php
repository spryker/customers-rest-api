<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomersRestApi\Persistence;

use Generated\Shared\Transfer\AddressTransfer;

interface CustomersRestApiRepositoryInterface
{
    /**
     * @param string $addressId
     * @param int $idCustomer
     *
     * @return \Generated\Shared\Transfer\AddressTransfer|null
     */
    public function findCustomerAddressById(string $addressId, int $idCustomer): ?AddressTransfer;
}
