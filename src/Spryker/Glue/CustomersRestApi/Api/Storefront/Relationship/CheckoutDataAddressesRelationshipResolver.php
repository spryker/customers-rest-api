<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CustomersRestApi\Api\Storefront\Relationship;

use Generated\Api\Storefront\CheckoutDataStorefrontResource;
use Generated\Api\Storefront\CustomersAddressesStorefrontResource;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\ApiPlatform\Exception\ApiPlatformContextException;
use Spryker\ApiPlatform\Relationship\AbstractRelationshipResolver;
use Spryker\Glue\CustomersRestApi\Api\Storefront\Mapper\CustomersAddressesResourceMapperInterface;
use Spryker\Service\Serializer\SerializerServiceInterface;

class CheckoutDataAddressesRelationshipResolver extends AbstractRelationshipResolver
{
    protected const string KEY_ADDRESS = 'address';

    protected const string KEY_CUSTOMER_REFERENCE = 'customerReference';

    public function __construct(
        protected CustomersAddressesResourceMapperInterface $customersAddressesResourceMapper,
        protected SerializerServiceInterface $serializer,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\ApiPlatformContextException When a parent resource is
     *     not a {@see CheckoutDataStorefrontResource} — this resolver is wired only to that resource
     *     and any other parent type indicates a configuration error in `checkout-data.resource.yml`.
     *
     * @return array<\Generated\Api\Storefront\CustomersAddressesStorefrontResource>
     */
    protected function resolveRelationship(): array
    {
        $resources = [];

        foreach ($this->getParentResources() as $parent) {
            if (!$parent instanceof CheckoutDataStorefrontResource) {
                throw new ApiPlatformContextException(sprintf(
                    'Resolver "%s" can only resolve "addresses" for parents of type "%s", got "%s".',
                    static::class,
                    CheckoutDataStorefrontResource::class,
                    get_debug_type($parent),
                ));
            }

            foreach ($parent->addressesRelationshipData ?? [] as $addressData) {
                $resources[] = $this->buildAddressResource($addressData);
            }
        }

        return $resources;
    }

    /**
     * @param array<string, mixed> $addressData
     */
    protected function buildAddressResource(array $addressData): CustomersAddressesStorefrontResource
    {
        $addressTransfer = (new AddressTransfer())->fromArray($addressData[static::KEY_ADDRESS] ?? [], true);
        $customerTransfer = (new CustomerTransfer())->setCustomerReference($addressData[static::KEY_CUSTOMER_REFERENCE] ?? null);

        return $this->serializer->denormalize(
            $this->customersAddressesResourceMapper->mapAddressTransferToResourceData($addressTransfer, $customerTransfer),
            CustomersAddressesStorefrontResource::class,
        );
    }
}
