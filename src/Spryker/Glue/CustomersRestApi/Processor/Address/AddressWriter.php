<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CustomersRestApi\Processor\Address;

use Generated\Shared\Transfer\AddressesTransfer;
use Generated\Shared\Transfer\AddressTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\RestAddressAttributesTransfer;
use Spryker\Glue\CustomersRestApi\Dependency\Client\CustomersRestApiToCustomerClientInterface;
use Spryker\Glue\CustomersRestApi\Processor\Mapper\AddressResourceMapperInterface;
use Spryker\Glue\CustomersRestApi\Processor\RestResponseBuilder\AddressRestResponseBuilderInterface;
use Spryker\Glue\CustomersRestApi\Processor\Validation\RestApiErrorInterface;
use Spryker\Glue\CustomersRestApi\Processor\Validation\RestApiValidatorInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

class AddressWriter implements AddressWriterInterface
{
    /**
     * @var \Spryker\Glue\CustomersRestApi\Dependency\Client\CustomersRestApiToCustomerClientInterface
     */
    protected $customerClient;

    /**
     * @var \Spryker\Glue\CustomersRestApi\Processor\Mapper\AddressResourceMapperInterface
     */
    protected $addressesResourceMapper;

    /**
     * @var \Spryker\Glue\CustomersRestApi\Processor\Validation\RestApiErrorInterface
     */
    protected $restApiError;

    /**
     * @var \Spryker\Glue\CustomersRestApi\Processor\Validation\RestApiValidatorInterface
     */
    protected $restApiValidator;

    /**
     * @var \Spryker\Glue\CustomersRestApi\Processor\Address\AddressReaderInterface
     */
    protected $addressReader;

    /**
     * @var \Spryker\Glue\CustomersRestApi\Processor\RestResponseBuilder\AddressRestResponseBuilderInterface
     */
    protected $addressRestResponseBuilder;

    public function __construct(
        CustomersRestApiToCustomerClientInterface $customerClient,
        AddressReaderInterface $addressReader,
        AddressResourceMapperInterface $addressesResourceMapper,
        RestApiErrorInterface $restApiError,
        RestApiValidatorInterface $restApiValidator,
        AddressRestResponseBuilderInterface $addressRestResponseBuilder
    ) {
        $this->customerClient = $customerClient;
        $this->addressReader = $addressReader;
        $this->addressesResourceMapper = $addressesResourceMapper;
        $this->restApiError = $restApiError;
        $this->restApiValidator = $restApiValidator;
        $this->addressRestResponseBuilder = $addressRestResponseBuilder;
    }

    public function createAddress(RestRequestInterface $restRequest, RestAddressAttributesTransfer $addressAttributesTransfer): RestResponseInterface
    {
        $restResponse = $this->addressRestResponseBuilder->createRestResponse();
        if (!$this->restApiValidator->isSameCustomerReference($restRequest)) {
            return $this->restApiError->addCustomerUnauthorizedError($restResponse);
        }

        $addressTransfer = $this->addressesResourceMapper->mapRestAddressAttributesTransferToAddressTransfer($addressAttributesTransfer);
        $addressTransfer->setFkCustomer((int)$restRequest->getRestUser()->getSurrogateIdentifier());

        $customerTransfer = $this->customerClient->createAddressAndUpdateCustomerDefaultAddresses($addressTransfer);
        $lastAddedAddress = $this->getLastAddedAddress($customerTransfer->getAddresses());

        $restResponse->addResource($this->getAddressResource($lastAddedAddress, $customerTransfer));

        return $restResponse;
    }

    public function updateAddress(RestRequestInterface $restRequest, RestAddressAttributesTransfer $addressAttributesTransfer): RestResponseInterface
    {
        $restResponse = $this->addressRestResponseBuilder->createRestResponse();
        if (!$restRequest->getResource()->getId()) {
            return $this->restApiError->addAddressUuidMissingError($restResponse);
        }

        if (!$this->restApiValidator->isSameCustomerReference($restRequest)) {
            return $this->restApiError->addCustomerUnauthorizedError($restResponse);
        }

        $addressTransfer = $this->addressReader->findAddressByUuid($restRequest, $restRequest->getResource()->getId());

        if (!$addressTransfer) {
            return $this->restApiError->addAddressNotFoundError($restResponse);
        }

        $addressTransfer->fromArray($addressAttributesTransfer->modifiedToArray(), true);

        $customerTransfer = $this->customerClient->updateAddressAndCustomerDefaultAddresses($addressTransfer);
        $modifiedAddressTransfer = $this->getModifiedAddress($addressTransfer, $customerTransfer);

        return $restResponse->addResource($this->getAddressResource($modifiedAddressTransfer, $customerTransfer));
    }

    public function deleteAddress(RestRequestInterface $restRequest): RestResponseInterface
    {
        $restResponse = $this->addressRestResponseBuilder->createRestResponse();
        if (!$restRequest->getResource()->getId()) {
            return $this->restApiError->addAddressUuidMissingError($restResponse);
        }

        if (!$this->restApiValidator->isSameCustomerReference($restRequest)) {
            return $this->restApiError->addCustomerUnauthorizedError($restResponse);
        }

        $addressTransfer = $this->addressReader->findAddressByUuid($restRequest, $restRequest->getResource()->getId());

        if (!$addressTransfer) {
            return $this->restApiError->addAddressNotFoundError($restResponse);
        }

        $this->customerClient->deleteAddress($addressTransfer);

        return $restResponse;
    }

    /**
     * @param \Generated\Shared\Transfer\AddressesTransfer $addressesTransfer
     *
     * @return \Generated\Shared\Transfer\AddressTransfer|mixed
     */
    protected function getLastAddedAddress(AddressesTransfer $addressesTransfer)
    {
        $lastAddedAddress = new AddressTransfer();
        foreach ($addressesTransfer->getAddresses() as $addressTransfer) {
            if ($addressTransfer->getIdCustomerAddress() > $lastAddedAddress->getIdCustomerAddress()) {
                $lastAddedAddress = $addressTransfer;
            }
        }

        return $lastAddedAddress;
    }

    protected function getModifiedAddress(
        AddressTransfer $modifiedAddressTransfer,
        CustomerTransfer $customerTransfer
    ): AddressTransfer {
        foreach ($customerTransfer->getAddresses()->getAddresses() as $addressTransfer) {
            if ($addressTransfer->getIdCustomerAddress() === $modifiedAddressTransfer->getIdCustomerAddress()) {
                return $addressTransfer;
            }
        }

        return $modifiedAddressTransfer;
    }

    protected function getAddressResource(
        AddressTransfer $addressTransfer,
        CustomerTransfer $customerTransfer
    ): RestResourceInterface {
        $restAddressAttributesTransfer = $this->addressesResourceMapper
            ->mapAddressTransferToRestAddressAttributesTransfer($addressTransfer, $customerTransfer);

        return $this->addressRestResponseBuilder->createAddressRestResource(
            $addressTransfer->getUuid(),
            $customerTransfer->getCustomerReference(),
            $restAddressAttributesTransfer,
        );
    }
}
