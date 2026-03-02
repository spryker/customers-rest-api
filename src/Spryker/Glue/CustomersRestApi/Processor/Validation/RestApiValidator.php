<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CustomersRestApi\Processor\Validation;

use Generated\Shared\Transfer\CustomerResponseTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\RestCustomerPasswordAttributesTransfer;
use Generated\Shared\Transfer\RestCustomersAttributesTransfer;
use Spryker\Glue\CustomersRestApi\CustomersRestApiConfig;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;

class RestApiValidator implements RestApiValidatorInterface
{
    /**
     * @var string
     */
    protected const CUSTOMERS_GENDER_ENUM_MALE = 'Male';

    /**
     * @var string
     */
    protected const CUSTOMERS_GENDER_ENUM_FEMALE = 'Female';

    /**
     * @var array<string>
     */
    public const CUSTOMERS_GENDERS_ENUM = [
        self::CUSTOMERS_GENDER_ENUM_MALE,
        self::CUSTOMERS_GENDER_ENUM_FEMALE,
    ];

    /**
     * @var \Spryker\Glue\CustomersRestApi\Processor\Validation\RestApiErrorInterface
     */
    protected $apiErrors;

    public function __construct(RestApiErrorInterface $apiErrors)
    {
        $this->apiErrors = $apiErrors;
    }

    public function validateCustomerResponseTransfer(
        CustomerResponseTransfer $customerResponseTransfer,
        RestRequestInterface $restRequest,
        RestResponseInterface $restResponse
    ): RestResponseInterface {
        if (!$customerResponseTransfer->getHasCustomer()) {
            return $this->apiErrors->addCustomerNotFoundError($restResponse);
        }

        if (!$this->isSameCustomerReference($restRequest)) {
            return $this->apiErrors->addCustomerUnauthorizedError($restResponse);
        }

        return $restResponse;
    }

    public function validateCustomerGender(
        RestCustomersAttributesTransfer $restCustomersAttributesTransfer,
        RestResponseInterface $restResponse
    ): RestResponseInterface {
        if (count($restResponse->getErrors()) > 0) {
            return $restResponse;
        }

        if (
            $restCustomersAttributesTransfer->isPropertyModified(CustomerTransfer::GENDER) &&
            !in_array($restCustomersAttributesTransfer->getGender(), static::CUSTOMERS_GENDERS_ENUM)
        ) {
            return $this->apiErrors->addNotValidGenderError($restResponse);
        }

        return $restResponse;
    }

    public function validatePassword(
        RestCustomerPasswordAttributesTransfer $passwordAttributesTransfer,
        RestResponseInterface $restResponse
    ): RestResponseInterface {
        if (count($restResponse->getErrors()) > 0) {
            return $restResponse;
        }

        if (!$this->assertPasswordNotEmpty($passwordAttributesTransfer)) {
            return $this->apiErrors->addPasswordNotValidError($restResponse);
        }

        if (!$this->assertPasswordsAreIdentical($passwordAttributesTransfer)) {
            return $this->apiErrors->addPasswordsDoNotMatchError(
                $restResponse,
                RestCustomerPasswordAttributesTransfer::NEW_PASSWORD,
                RestCustomerPasswordAttributesTransfer::CONFIRM_PASSWORD,
            );
        }

        return $restResponse;
    }

    public function isSameCustomerReference(RestRequestInterface $restRequest): bool
    {
        $customerResource = $restRequest->findParentResourceByType(CustomersRestApiConfig::RESOURCE_CUSTOMERS) ?? $restRequest->getResource();

        return $restRequest->getRestUser()->getNaturalIdentifier() === $customerResource->getId();
    }

    protected function assertPasswordsAreIdentical(RestCustomerPasswordAttributesTransfer $passwordAttributesTransfer): bool
    {
        return $passwordAttributesTransfer->getNewPassword() === $passwordAttributesTransfer->getConfirmPassword();
    }

    protected function assertPasswordNotEmpty(RestCustomerPasswordAttributesTransfer $passwordAttributesTransfer): bool
    {
        return mb_strlen($passwordAttributesTransfer->getNewPassword()) > 1;
    }
}
