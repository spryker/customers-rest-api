<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\CustomersRestApi\Business\Addresses\Mapper;

use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\QuoteTransfer;
use Generated\Shared\Transfer\RestAddressTransfer;
use Generated\Shared\Transfer\RestCheckoutRequestAttributesTransfer;
use Generated\Shared\Transfer\RestCustomerTransfer;
use Spryker\Zed\CustomersRestApi\Dependency\Facade\CustomersRestApiToCustomerFacadeInterface;

class AddressesQuoteMapper implements AddressesQuoteMapperInterface
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
     * @param \Generated\Shared\Transfer\RestCheckoutRequestAttributesTransfer $restCheckoutRequestAttributesTransfer
     * @param \Generated\Shared\Transfer\QuoteTransfer $quoteTransfer
     *
     * @return \Generated\Shared\Transfer\QuoteTransfer
     */
    public function mapAddressesToQuote(
        RestCheckoutRequestAttributesTransfer $restCheckoutRequestAttributesTransfer,
        QuoteTransfer $quoteTransfer
    ): QuoteTransfer {
        $restQuoteRequestTransfer = $restCheckoutRequestAttributesTransfer->getCart();
        if ($restQuoteRequestTransfer->getBillingAddress() !== null) {
            $billingAddress = $this->getAddressTransfer($restQuoteRequestTransfer->getBillingAddress(), $restQuoteRequestTransfer->getCustomer());

            $quoteTransfer->setBillingAddress($billingAddress);
        }

        if ($restQuoteRequestTransfer->getShippingAddress() !== null) {
            $shippingAddress = $this->getAddressTransfer($restQuoteRequestTransfer->getShippingAddress(), $restQuoteRequestTransfer->getCustomer());

            $quoteTransfer->setShippingAddress($shippingAddress);
        }

        return $quoteTransfer;
    }

    /**
     * @param \Generated\Shared\Transfer\RestAddressTransfer $restAddressTransfer
     * @param \Generated\Shared\Transfer\RestCustomerTransfer $restCustomerTransfer
     *
     * @return \Generated\Shared\Transfer\AddressTransfer
     */
    protected function getAddressTransfer(RestAddressTransfer $restAddressTransfer, RestCustomerTransfer $restCustomerTransfer): AddressTransfer
    {
        if (!$restCustomerTransfer->getIdCustomer()) {
            return (new AddressTransfer())
                ->fromArray($restAddressTransfer->toArray(), true);
        }

        if ($restAddressTransfer->getId()) {
            $addressTransfer = $this->getAddressByUuid($restAddressTransfer, $restCustomerTransfer);

            if ($addressTransfer !== null) {
                return $addressTransfer;
            }
        }

        return (new AddressTransfer())->fromArray($restAddressTransfer->toArray(), true);
    }

    /**
     * @param \Generated\Shared\Transfer\RestAddressTransfer $restAddressTransfer
     * @param \Generated\Shared\Transfer\RestCustomerTransfer $restCustomerTransfer
     *
     * @return \Generated\Shared\Transfer\AddressTransfer|null
     */
    protected function getAddressByUuid(RestAddressTransfer $restAddressTransfer, RestCustomerTransfer $restCustomerTransfer): ?AddressTransfer
    {
        $customerTransfer = (new CustomerTransfer())->setIdCustomer($restCustomerTransfer->getIdCustomer());

        $addressTransfers = $this->customerFacade->getAddresses($customerTransfer);

        foreach ($addressTransfers->getAddresses() as $addressTransfer) {
            if ($addressTransfer->getUuid() === $restAddressTransfer->getId()) {
                return $addressTransfer;
            }
        }

        return null;
    }
}