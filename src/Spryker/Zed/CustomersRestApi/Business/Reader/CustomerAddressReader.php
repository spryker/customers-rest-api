<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomersRestApi\Business\Reader;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\RestAddressTransfer;
use Spryker\Zed\CustomersRestApi\Dependency\Facade\CustomersRestApiToCustomerFacadeInterface;

class CustomerAddressReader implements CustomerAddressReaderInterface
{
    /**
     * @var \Spryker\Zed\CustomersRestApi\Dependency\Facade\CustomersRestApiToCustomerFacadeInterface
     */
    protected $customerFacade;

    /**
     * @param \Spryker\Zed\CustomersRestApi\Dependency\Facade\CustomersRestApiToCustomerFacadeInterface $customerFacade
     */
    public function __construct(CustomersRestApiToCustomerFacadeInterface $customerFacade)
    {
        $this->customerFacade = $customerFacade;
    }

    /**
     * @param \Generated\Shared\Transfer\RestAddressTransfer $restAddressTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\AddressTransfer
     */
    public function getCustomerAddress(
        RestAddressTransfer $restAddressTransfer,
        QuoteTransfer $quoteTransfer
    ): AddressTransfer {
        $addressesTransfer = $this->customerFacade->getAddresses($quoteTransfer->getCustomer());

        foreach ($addressesTransfer->getAddresses() as $addressTransfer) {
            if ($addressTransfer->getUuid() !== $restAddressTransfer->getId()) {
                continue;
            }

            $addressTransfer->setCountry(null);

            return $addressTransfer;
        }

        return (new AddressTransfer())->fromArray($restAddressTransfer->toArray(), true);
    }
}
