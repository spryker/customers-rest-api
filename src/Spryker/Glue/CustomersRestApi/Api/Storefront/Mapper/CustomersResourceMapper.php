<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CustomersRestApi\Api\Storefront\Mapper;

use Generated\Api\Storefront\CustomersStorefrontResource;
use Generated\Shared\Transfer\CustomerTransfer;

class CustomersResourceMapper implements CustomersResourceMapperInterface
{
    /**
     * {@inheritDoc}
     *
     * @return array<string, mixed>
     */
    public function mapCustomerTransferToResourceData(CustomerTransfer $customerTransfer): array
    {
        return [
            'customerReference' => $customerTransfer->getCustomerReference(),
            'email' => $customerTransfer->getEmail(),
            'salutation' => $customerTransfer->getSalutation(),
            'firstName' => $customerTransfer->getFirstName(),
            'lastName' => $customerTransfer->getLastName(),
            'gender' => $customerTransfer->getGender(),
            'dateOfBirth' => $customerTransfer->getDateOfBirth(),
            'phone' => $customerTransfer->getPhone(),
            'createdAt' => $customerTransfer->getCreatedAt(),
            'updatedAt' => $customerTransfer->getUpdatedAt(),
            'anonymizedAt' => $customerTransfer->getAnonymizedAt(),
        ];
    }

    public function mapResourceToCustomerTransfer(
        CustomersStorefrontResource $resource,
        CustomerTransfer $customerTransfer,
        bool $includePassword = true,
    ): CustomerTransfer {
        $customerTransfer->setEmail($resource->email);
        $customerTransfer->setSalutation($resource->salutation);
        $customerTransfer->setFirstName($resource->firstName);
        $customerTransfer->setLastName($resource->lastName);
        $customerTransfer->setGender($resource->gender);
        $customerTransfer->setDateOfBirth($resource->dateOfBirth);
        $customerTransfer->setPhone($resource->phone);

        if ($includePassword) {
            $customerTransfer->setPassword($resource->password);
        }

        return $customerTransfer;
    }
}
