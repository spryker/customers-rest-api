<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CustomersRestApi\Processor\RestResponseBuilder;

use Generated\Shared\Transfer\RestAddressAttributesTransfer;
use Spryker\Glue\CustomersRestApi\CustomersRestApiConfig;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestLinkInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

class AddressRestResponseBuilder implements AddressRestResponseBuilderInterface
{
    /**
     * @var \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface
     */
    protected $restResourceBuilder;

    public function __construct(RestResourceBuilderInterface $restResourceBuilder)
    {
        $this->restResourceBuilder = $restResourceBuilder;
    }

    public function createRestResponse(): RestResponseInterface
    {
        return $this->restResourceBuilder->createRestResponse();
    }

    public function createAddressRestResource(
        string $addressUuid,
        string $customerReference,
        RestAddressAttributesTransfer $restAddressAttributesTransfer
    ): RestResourceInterface {
        return $this->restResourceBuilder
            ->createRestResource(
                CustomersRestApiConfig::RESOURCE_ADDRESSES,
                $addressUuid,
                $restAddressAttributesTransfer,
            )
            ->addLink(
                RestLinkInterface::LINK_SELF,
                $this->createSelfLink($addressUuid, $customerReference),
            );
    }

    protected function createSelfLink(string $addressUuid, string $customerReference): string
    {
        return sprintf(
            '%s/%s/%s/%s',
            CustomersRestApiConfig::RESOURCE_CUSTOMERS,
            $customerReference,
            CustomersRestApiConfig::RESOURCE_ADDRESSES,
            $addressUuid,
        );
    }
}
