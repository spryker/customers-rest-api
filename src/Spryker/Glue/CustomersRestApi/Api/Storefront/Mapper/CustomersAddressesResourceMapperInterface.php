<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CustomersRestApi\Api\Storefront\Mapper;

use Generated\Api\Storefront\CustomersAddressesStorefrontResource;
use Generated\Shared\Transfer\AddressesTransfer;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CustomerTransfer;

interface CustomersAddressesResourceMapperInterface
{
    /**
     * Prepares the array payload used to denormalize a `CustomersAddressesStorefrontResource`
     * from an `AddressTransfer`. Resolves `isDefaultShipping`/`isDefaultBilling` against the
     * given customer's default-shipping/billing address ids.
     *
     * @return array<string, mixed>
     */
    public function mapAddressTransferToResourceData(AddressTransfer $addressTransfer, CustomerTransfer $customerTransfer): array;

    /**
     * Prepares the array of resource data payloads for every address in the given collection.
     *
     * @return array<int, array<string, mixed>>
     */
    public function mapAddressesTransferToResourceDataArray(AddressesTransfer $addressesTransfer, CustomerTransfer $customerTransfer): array;

    /**
     * Copies the writable attributes of a `CustomersAddressesStorefrontResource` onto an existing
     * `AddressTransfer`. Skips `country` and `region` because the resource exposes them as plain
     * strings while the transfer expects object/CountryTransfer types.
     */
    public function mapResourceToAddressTransfer(
        CustomersAddressesStorefrontResource $resource,
        AddressTransfer $addressTransfer,
    ): AddressTransfer;

    /**
     * Locates the most-recently-created address in {@see CustomerTransfer::getAddresses()} by the
     * highest `idCustomerAddress` (auto-increment id) — same heuristic legacy
     * `AddressWriter::getLastAddedAddress` uses.
     */
    public function getLastAddedAddress(CustomerTransfer $customerTransfer): ?AddressTransfer;
}
