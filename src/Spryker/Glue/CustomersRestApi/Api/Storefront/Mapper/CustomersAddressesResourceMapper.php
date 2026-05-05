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

class CustomersAddressesResourceMapper implements CustomersAddressesResourceMapperInterface
{
    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    public function mapAddressTransferToResourceData(AddressTransfer $addressTransfer, CustomerTransfer $customerTransfer): array
    {
        $data = [
            'uuid' => $addressTransfer->getUuid(),
            'salutation' => $addressTransfer->getSalutation(),
            'firstName' => $addressTransfer->getFirstName(),
            'lastName' => $addressTransfer->getLastName(),
            'address1' => $addressTransfer->getAddress1(),
            'address2' => $addressTransfer->getAddress2(),
            'address3' => $addressTransfer->getAddress3(),
            'company' => $addressTransfer->getCompany(),
            'city' => $addressTransfer->getCity(),
            'zipCode' => $addressTransfer->getZipCode(),
            'phone' => $addressTransfer->getPhone(),
            'comment' => $addressTransfer->getComment(),
            'isDefaultShipping' => $this->isDefaultAddress(
                $customerTransfer->getDefaultShippingAddress(),
                $addressTransfer->getIdCustomerAddress(),
            ),
            'isDefaultBilling' => $this->isDefaultAddress(
                $customerTransfer->getDefaultBillingAddress(),
                $addressTransfer->getIdCustomerAddress(),
            ),
        ];

        if ($addressTransfer->getCountry()) {
            $data['country'] = $addressTransfer->getCountry()->getName();
            $data['iso2Code'] = $addressTransfer->getCountry()->getIso2Code();
        }

        if ($addressTransfer->getRegion()) {
            $data['region'] = $addressTransfer->getRegion();
        }

        return $data;
    }

    /**
     * {@inheritDoc}
     *
     * @return array<int, array<string, mixed>>
     */
    public function mapAddressesTransferToResourceDataArray(AddressesTransfer $addressesTransfer, CustomerTransfer $customerTransfer): array
    {
        $resources = [];

        foreach ($addressesTransfer->getAddresses() as $addressTransfer) {
            $resources[] = $this->mapAddressTransferToResourceData($addressTransfer, $customerTransfer);
        }

        return $resources;
    }

    public function mapResourceToAddressTransfer(
        CustomersAddressesStorefrontResource $resource,
        AddressTransfer $addressTransfer,
    ): AddressTransfer {
        $data = $resource->toArray();

        // The resource exposes `country` and `region` as plain strings (display names derived from
        // iso2Code on read). On AddressTransfer they are object/CountryTransfer types. Forwarding
        // the strings through fromArray() would overwrite the object on PATCH and break later
        // serialization (`getCountry()->getName()` on a string).
        unset($data['country'], $data['region']);

        return $addressTransfer->fromArray($data, true);
    }

    public function getLastAddedAddress(CustomerTransfer $customerTransfer): ?AddressTransfer
    {
        $addresses = $customerTransfer->getAddresses();

        if ($addresses === null) {
            return null;
        }

        $lastAdded = null;

        foreach ($addresses->getAddresses() as $addressTransfer) {
            if ($lastAdded === null || (int)$addressTransfer->getIdCustomerAddress() > (int)$lastAdded->getIdCustomerAddress()) {
                $lastAdded = $addressTransfer;
            }
        }

        return $lastAdded;
    }

    protected function isDefaultAddress(string|int|null $defaultAddressId, ?int $addressId): bool
    {
        if ($defaultAddressId === null || $addressId === null) {
            return false;
        }

        return (int)$defaultAddressId === $addressId;
    }
}
