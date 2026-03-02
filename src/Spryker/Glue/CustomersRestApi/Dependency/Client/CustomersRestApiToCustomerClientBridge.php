<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CustomersRestApi\Dependency\Client;

use Generated\Shared\Transfer\AddressesTransfer;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CustomerResponseTransfer;
use Generated\Shared\Transfer\CustomerTransfer;

class CustomersRestApiToCustomerClientBridge implements CustomersRestApiToCustomerClientInterface
{
    /**
     * @var \Spryker\Client\Customer\CustomerClientInterface
     */
    protected $customerClient;

    /**
     * @param \Spryker\Client\Customer\CustomerClientInterface $customerClient
     */
    public function __construct($customerClient)
    {
        $this->customerClient = $customerClient;
    }

    public function registerCustomer(CustomerTransfer $customerTransfer): CustomerResponseTransfer
    {
        return $this->customerClient->registerCustomer($customerTransfer);
    }

    public function sendPasswordRestoreMail(CustomerTransfer $customerTransfer): CustomerResponseTransfer
    {
        return $this->customerClient->sendPasswordRestoreMail($customerTransfer);
    }

    public function restorePassword(CustomerTransfer $customerTransfer): CustomerResponseTransfer
    {
        return $this->customerClient->restorePassword($customerTransfer);
    }

    public function findCustomerByReference(CustomerTransfer $customerTransfer): CustomerResponseTransfer
    {
        return $this->customerClient->findCustomerByReference($customerTransfer);
    }

    public function getAddresses(CustomerTransfer $customerTransfer): AddressesTransfer
    {
        return $this->customerClient->getAddresses($customerTransfer);
    }

    public function createAddress(AddressTransfer $addressTransfer): AddressTransfer
    {
        return $this->customerClient->createAddress($addressTransfer);
    }

    public function createAddressAndUpdateCustomerDefaultAddresses(AddressTransfer $addressTransfer): CustomerTransfer
    {
        return $this->customerClient->createAddressAndUpdateCustomerDefaultAddresses($addressTransfer);
    }

    public function updateAddressAndCustomerDefaultAddresses(AddressTransfer $addressTransfer): CustomerTransfer
    {
        return $this->customerClient->updateAddressAndCustomerDefaultAddresses($addressTransfer);
    }

    public function updateCustomerPassword(CustomerTransfer $customerTransfer): CustomerResponseTransfer
    {
        return $this->customerClient->updateCustomerPassword($customerTransfer);
    }

    public function deleteAddress(AddressTransfer $addressTransfer): AddressTransfer
    {
        return $this->customerClient->deleteAddress($addressTransfer);
    }

    public function updateCustomer(CustomerTransfer $customerTransfer): CustomerResponseTransfer
    {
        return $this->customerClient->updateCustomer($customerTransfer);
    }

    public function anonymizeCustomer(CustomerTransfer $customerTransfer): CustomerTransfer
    {
        return $this->customerClient->anonymizeCustomer($customerTransfer);
    }

    public function updateAddress(AddressTransfer $addressTransfer): AddressTransfer
    {
        return $this->customerClient->updateAddress($addressTransfer);
    }

    public function setCustomer(CustomerTransfer $customerTransfer): CustomerTransfer
    {
        return $this->customerClient->setCustomer($customerTransfer);
    }

    public function setCustomerRawData(CustomerTransfer $customerTransfer): CustomerTransfer
    {
        return $this->customerClient->setCustomerRawData($customerTransfer);
    }

    public function confirmCustomerRegistration(CustomerTransfer $customerTransfer): CustomerResponseTransfer
    {
        return $this->customerClient->confirmCustomerRegistration($customerTransfer);
    }
}
