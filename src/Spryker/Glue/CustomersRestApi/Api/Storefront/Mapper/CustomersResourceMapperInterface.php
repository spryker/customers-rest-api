<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CustomersRestApi\Api\Storefront\Mapper;

use Generated\Api\Storefront\CustomersStorefrontResource;
use Generated\Shared\Transfer\CustomerTransfer;

interface CustomersResourceMapperInterface
{
    /**
     * Prepares the array payload used to denormalize a `CustomersStorefrontResource` from a `CustomerTransfer`.
     *
     * @return array<string, mixed>
     */
    public function mapCustomerTransferToResourceData(CustomerTransfer $customerTransfer): array;

    /**
     * Copies the writable attributes of a `CustomersStorefrontResource` onto an existing `CustomerTransfer`.
     * When `$includePassword` is `false`, the password attribute is skipped (for update flows that do not
     * require a password change).
     */
    public function mapResourceToCustomerTransfer(
        CustomersStorefrontResource $resource,
        CustomerTransfer $customerTransfer,
        bool $includePassword = true,
    ): CustomerTransfer;
}
