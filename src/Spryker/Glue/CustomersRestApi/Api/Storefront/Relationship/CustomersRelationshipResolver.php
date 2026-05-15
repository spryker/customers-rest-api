<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CustomersRestApi\Api\Storefront\Relationship;

use Generated\Api\Storefront\CustomersStorefrontResource;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\ApiPlatform\Relationship\PerItemRelationshipResolverInterface;
use Spryker\Client\Customer\CustomerClientInterface;
use Spryker\Glue\CustomersRestApi\Api\Storefront\Mapper\CustomersResourceMapperInterface;
use Spryker\Service\Serializer\SerializerServiceInterface;

class CustomersRelationshipResolver implements PerItemRelationshipResolverInterface
{
    public function __construct(
        protected CustomerClientInterface $customerClient,
        protected CustomersResourceMapperInterface $customersResourceMapper,
        protected SerializerServiceInterface $serializer,
    ) {
    }

    /**
     * @param array<object> $parentResources
     * @param array<string, mixed> $context
     *
     * @return array<object>
     */
    public function resolve(array $parentResources, array $context): array
    {
        $allResources = [];

        foreach ($this->resolvePerItem($parentResources, $context) as $resources) {
            $allResources = array_merge($allResources, $resources);
        }

        return $allResources;
    }

    /**
     * @param array<object> $parentResources
     * @param array<string, mixed> $context
     *
     * @return array<string, array<object>>
     */
    public function resolvePerItem(array $parentResources, array $context): array
    {
        $result = [];

        foreach ($parentResources as $parentResource) {
            $uuid = $parentResource->uuid ?? null;
            $customerReference = $parentResource->customerReference ?? null;

            if ($uuid === null) {
                continue;
            }

            if ($customerReference === null) {
                $result[$uuid] = [];

                continue;
            }

            $customerTransfer = $this->resolveCustomerTransfer($customerReference, $parentResource->customerTransferData ?? null);

            if ($customerTransfer === null) {
                $result[$uuid] = [];

                continue;
            }

            $result[$uuid] = [$this->denormalizeToCustomerResource($customerTransfer)];
        }

        return $result;
    }

    protected function resolveCustomerTransfer(string $customerReference, ?object $customerTransferData): ?CustomerTransfer
    {
        if ($customerTransferData !== null) {
            return (new CustomerTransfer())->fromArray((array)$customerTransferData, true);
        }

        return $this->customerClient->findCustomerByReference(
            (new CustomerTransfer())->setCustomerReference($customerReference),
        )->getCustomerTransfer();
    }

    protected function denormalizeToCustomerResource(CustomerTransfer $customerTransfer): CustomersStorefrontResource
    {
        return $this->serializer->denormalize(
            $this->customersResourceMapper->mapCustomerTransferToResourceData($customerTransfer),
            CustomersStorefrontResource::class,
        );
    }
}
