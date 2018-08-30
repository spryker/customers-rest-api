<?php
/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CustomersRestApi\Processor\Mapper;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\RestAddressAttributesTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;

interface AddressesResourceMapperInterface
{
    /**
     * @param \Generated\Shared\Transfer\AddressTransfer $addressesTransfer
     * @param string $customerReference
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface
     */
    public function mapAddressTransferToRestResource(AddressTransfer $addressesTransfer, string $customerReference): RestResourceInterface;

    /**
     * @param \Generated\Shared\Transfer\RestAddressAttributesTransfer $restAddressAttributesTransfer
     *
     * @return \Generated\Shared\Transfer\AddressTransfer
     */
    public function mapRestAddressAttributesTransferToAddressTransfer(
        RestAddressAttributesTransfer $restAddressAttributesTransfer
    ): AddressTransfer;
}