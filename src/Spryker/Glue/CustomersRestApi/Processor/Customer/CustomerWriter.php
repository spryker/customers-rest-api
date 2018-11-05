<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CustomersRestApi\Processor\Customer;

use Generated\Shared\Transfer\CustomerResponseTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\RestCustomerPasswordAttributesTransfer;
use Generated\Shared\Transfer\RestCustomersAttributesTransfer;
use Spryker\Glue\CustomersRestApi\CustomersRestApiConfig;
use Spryker\Glue\CustomersRestApi\Dependency\Client\CustomersRestApiToCustomerClientInterface;
use Spryker\Glue\CustomersRestApi\Processor\Mapper\CustomerResourceMapperInterface;
use Spryker\Glue\CustomersRestApi\Processor\Validation\RestApiErrorInterface;
use Spryker\Glue\CustomersRestApi\Processor\Validation\RestApiValidatorInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Symfony\Component\HttpFoundation\Response;

class CustomerWriter implements CustomerWriterInterface
{
    protected const ERROR_MESSAGE_CUSTOMER_EMAIL_ALREADY_USED = 'customer.email.already.used';
    protected const ERROR_CUSTOMER_PASSWORD_INVALID = 'customer.password.invalid';

    /**
     * @var \Spryker\Glue\CustomersRestApi\Dependency\Client\CustomersRestApiToCustomerClientInterface
     */
    protected $customerClient;

    /**
     * @var \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface
     */
    protected $restResourceBuilder;

    /**
     * @var \Spryker\Glue\CustomersRestApi\Processor\Mapper\CustomerResourceMapperInterface
     */
    protected $customerResourceMapper;

    /**
     * @var \Spryker\Glue\CustomersRestApi\Processor\Validation\RestApiErrorInterface
     */
    protected $restApiError;

    /**
     * @var \Spryker\Glue\CustomersRestApi\Processor\Validation\RestApiValidatorInterface
     */
    protected $restApiValidator;

    /**
     * @var \Spryker\Glue\CustomersRestApi\Processor\Customer\CustomerReaderInterface
     */
    protected $customerReader;

    /**
     * @var \Spryker\Glue\CustomersRestApiExtension\Dependency\Plugin\CustomerPostRegisterPluginInterface[]
     */
    protected $customerPostRegisterPlugins;

    /**
     * @param \Spryker\Glue\CustomersRestApi\Dependency\Client\CustomersRestApiToCustomerClientInterface $customerClient
     * @param \Spryker\Glue\CustomersRestApi\Processor\Customer\CustomerReaderInterface $customerReader
     * @param \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResourceBuilderInterface $restResourceBuilder
     * @param \Spryker\Glue\CustomersRestApi\Processor\Mapper\CustomerResourceMapperInterface $customerResourceMapper
     * @param \Spryker\Glue\CustomersRestApi\Processor\Validation\RestApiErrorInterface $restApiError
     * @param \Spryker\Glue\CustomersRestApi\Processor\Validation\RestApiValidatorInterface $restApiValidator
     * @param \Spryker\Glue\CustomersRestApiExtension\Dependency\Plugin\CustomerPostRegisterPluginInterface[] $customerPostRegisterPlugins
     */
    public function __construct(
        CustomersRestApiToCustomerClientInterface $customerClient,
        CustomerReaderInterface $customerReader,
        RestResourceBuilderInterface $restResourceBuilder,
        CustomerResourceMapperInterface $customerResourceMapper,
        RestApiErrorInterface $restApiError,
        RestApiValidatorInterface $restApiValidator,
        array $customerPostRegisterPlugins
    ) {
        $this->customerClient = $customerClient;
        $this->customerReader = $customerReader;
        $this->restResourceBuilder = $restResourceBuilder;
        $this->customerResourceMapper = $customerResourceMapper;
        $this->restApiError = $restApiError;
        $this->restApiValidator = $restApiValidator;
        $this->customerPostRegisterPlugins = $customerPostRegisterPlugins;
    }

    /**
     * @param \Generated\Shared\Transfer\RestCustomersAttributesTransfer $restCustomersAttributesTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function registerCustomer(
        RestCustomersAttributesTransfer $restCustomersAttributesTransfer
    ): RestResponseInterface {
        $restResponse = $this->restResourceBuilder->createRestResponse();

        if (!$restCustomersAttributesTransfer->getAcceptedTerms()) {
            return $this->restApiError->addNotAcceptedTermsError($restResponse);
        }

        $customerTransfer = (new CustomerTransfer())->fromArray($restCustomersAttributesTransfer->toArray(), true);
        $customerResponseTransfer = $this->customerClient->registerCustomer($customerTransfer);

        if (!$customerResponseTransfer->getIsSuccess()) {
            foreach ($customerResponseTransfer->getErrors() as $error) {
                if ($error->getMessage() === static::ERROR_MESSAGE_CUSTOMER_EMAIL_ALREADY_USED) {
                    return $this->restApiError->addCustomerAlreadyExistsError($restResponse);
                }
                return $this->restApiError->addCustomerCantRegisterMessageError($restResponse, $error->getMessage());
            }
        }

        $customerTransfer = $customerResponseTransfer->getCustomerTransfer();
        $customerTransfer = $this->executeCustomerPostRegisterPlugins($customerTransfer);

        $restCustomersResponseAttributesTransfer = $this->customerResourceMapper
            ->mapCustomerTransferToRestCustomersResponseAttributesTransfer($customerTransfer);

        $restResource = $this->restResourceBuilder->createRestResource(
            CustomersRestApiConfig::RESOURCE_CUSTOMERS,
            $customerResponseTransfer->getCustomerTransfer()->getCustomerReference(),
            $restCustomersResponseAttributesTransfer
        );

        return $restResponse->addResource($restResource);
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     * @param \Generated\Shared\Transfer\RestCustomersAttributesTransfer $restCustomerAttributesTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function updateCustomer(
        RestRequestInterface $restRequest,
        RestCustomersAttributesTransfer $restCustomerAttributesTransfer
    ): RestResponseInterface {
        $restResponse = $this->restResourceBuilder->createRestResponse();

        $customerResponseTransfer = $this->customerReader->findCustomer($restRequest);

        if (!$this->restApiValidator->isSameCustomerReference($restRequest)) {
            return $this->restApiError->addCustomerUnauthorizedError($restResponse);
        }

        $restResponse = $this->restApiValidator->validateCustomerGender($restCustomerAttributesTransfer, $restResponse);

        if (count($restResponse->getErrors()) > 0) {
            return $restResponse;
        }

        $customerResponseTransfer->getCustomerTransfer()->fromArray(
            $this->getCustomerData($restCustomerAttributesTransfer)
        );

        $customerResponseTransfer = $this->customerClient->updateCustomer($customerResponseTransfer->getCustomerTransfer());

        if (!$customerResponseTransfer->getIsSuccess()) {
            return $this->restApiError->addCustomerNotSavedError($restResponse);
        }

        $restCustomersResponseAttributesTransfer = $this->customerResourceMapper
            ->mapCustomerTransferToRestCustomersResponseAttributesTransfer(
                $customerResponseTransfer->getCustomerTransfer()
            );

        $restResource = $this->restResourceBuilder->createRestResource(
            CustomersRestApiConfig::RESOURCE_CUSTOMERS,
            $customerResponseTransfer->getCustomerTransfer()->getCustomerReference(),
            $restCustomersResponseAttributesTransfer
        );

        return $restResponse->addResource($restResource);
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     * @param \Generated\Shared\Transfer\RestCustomerPasswordAttributesTransfer $passwordAttributesTransfer
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function updateCustomerPassword(
        RestRequestInterface $restRequest,
        RestCustomerPasswordAttributesTransfer $passwordAttributesTransfer
    ): RestResponseInterface {
        $restResponse = $this->restResourceBuilder->createRestResponse();
        $customerResponseTransfer = $this->customerReader->getCurrentCustomer($restRequest);

        if (!$customerResponseTransfer->getHasCustomer() || $restRequest->getResource()->getId()) {
            return $this->restApiError->addCustomerNotFoundError($restResponse);
        }

        $restResponse = $this->restApiValidator->validatePassword($passwordAttributesTransfer, $restResponse);

        if (count($restResponse->getErrors()) > 0) {
            return $restResponse;
        }

        $customerTransfer = $customerResponseTransfer->getCustomerTransfer();
        $customerTransfer->fromArray($passwordAttributesTransfer->toArray(), true);
        $restResponse = $this->setResponseError($this->customerClient->updateCustomerPassword($customerTransfer), $restResponse);

        return $restResponse->getErrors() ? $restResponse : $restResponse->setStatus(Response::HTTP_NO_CONTENT);
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerResponseTransfer $customerResponseTransfer
     * @param \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface $restResponse
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    protected function setResponseError(CustomerResponseTransfer $customerResponseTransfer, RestResponseInterface $restResponse)
    {
        foreach ($customerResponseTransfer->getErrors() as $error) {
            if ($error->getMessage() === static::ERROR_CUSTOMER_PASSWORD_INVALID) {
                return $this->restApiError->addPasswordNotValidError($restResponse);
            }

            return $this->restApiError->addPasswordChangeError($restResponse, $error->getMessage());
        }

        return $restResponse;
    }

    /**
     * @param \Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface $restRequest
     *
     * @return \Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface
     */
    public function anonymizeCustomer(RestRequestInterface $restRequest): RestResponseInterface
    {
        $restResponse = $this->restResourceBuilder->createRestResponse();

        if (!$restRequest->getResource()->getId()) {
            return $this->restApiError->addCustomerReferenceMissingError($restResponse);
        }

        $customerResponseTransfer = $this->customerReader->findCustomer($restRequest);

        $restResponse = $this->restApiValidator->validateCustomerResponseTransfer(
            $customerResponseTransfer,
            $restRequest,
            $restResponse
        );

        if (count($restResponse->getErrors()) > 0) {
            return $restResponse;
        }

        $this->customerClient->anonymizeCustomer($customerResponseTransfer->getCustomerTransfer());

        return $restResponse;
    }

    /**
     * @param \Generated\Shared\Transfer\RestCustomersAttributesTransfer $customerTransfer
     *
     * @return array
     */
    protected function getCustomerData(RestCustomersAttributesTransfer $customerTransfer): array
    {
        $customerData = $customerTransfer->modifiedToArray(true, true);

        return $this->cleanUpCustomerAttributes($customerData);
    }

    /**
     * Unsetting data, which should be updated by the system itself,
     * in other words, shouldn't be updated by the customer
     *
     * @param array $customerAttributes
     *
     * @return array
     */
    protected function cleanUpCustomerAttributes(array $customerAttributes): array
    {
        unset(
            $customerAttributes[RestCustomersAttributesTransfer::CREATED_AT],
            $customerAttributes[RestCustomersAttributesTransfer::UPDATED_AT]
        );

        return $customerAttributes;
    }

    /**
     * @param \Generated\Shared\Transfer\CustomerTransfer $customerTransfer
     *
     * @return \Generated\Shared\Transfer\CustomerTransfer
     */
    protected function executeCustomerPostRegisterPlugins(CustomerTransfer $customerTransfer): CustomerTransfer
    {
        foreach ($this->customerPostRegisterPlugins as $customerPostRegisterPlugin) {
            $customerTransfer = $customerPostRegisterPlugin->postRegister($customerTransfer);
        }

        return $customerTransfer;
    }
}
