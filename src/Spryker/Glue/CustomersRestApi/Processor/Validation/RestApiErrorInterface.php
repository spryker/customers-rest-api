<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CustomersRestApi\Processor\Validation;

use Generated\Shared\Transfer\CustomerResponseTransfer;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponseInterface;

interface RestApiErrorInterface
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE_CUSTOMER_EMAIL_ALREADY_USED = 'customer.email.already.used';

    /**
     * @var string
     */
    public const ERROR_MESSAGE_CUSTOMER_EMAIL_INVALID = 'customer.email.format.invalid';

    /**
     * @var string
     */
    public const ERROR_MESSAGE_CUSTOMER_EMAIL_LENGTH_EXCEEDED = 'customer.email.length.exceeded';

    /**
     * @var string
     */
    public const ERROR_CUSTOMER_PASSWORD_INVALID = 'customer.password.invalid';

    /**
     * @var string
     */
    public const ERROR_CUSTOMER_TOKEN_INVALID = 'customer.token.invalid';

    /**
     * @uses \Spryker\Zed\Customer\Business\CustomerPasswordPolicy\LengthCustomerPasswordPolicy::GLOSSARY_KEY_PASSWORD_POLICY_ERROR_MAX
     *
     * @var string
     */
    public const ERROR_CUSTOMER_PASSWORD_TOO_LONG = 'customer.password.error.max_length';

    /**
     * @uses \Spryker\Zed\Customer\Business\CustomerPasswordPolicy\LengthCustomerPasswordPolicy::GLOSSARY_KEY_PASSWORD_POLICY_ERROR_MIN
     *
     * @var string
     */
    public const ERROR_CUSTOMER_PASSWORD_TOO_SHORT = 'customer.password.error.min_length';

    /**
     * @uses \Spryker\Zed\Customer\Business\CustomerPasswordPolicy\CharacterSetCustomerPasswordPolicy::GLOSSARY_KEY_PASSWORD_POLICY_ERROR_CHARACTER_SET
     *
     * @var string
     */
    public const ERROR_CUSTOMER_PASSWORD_CHARACTER_SET = 'customer.password.error.character_set';

    /**
     * @uses \Spryker\Zed\Customer\Business\CustomerPasswordPolicy\SequenceCustomerPasswordPolicy::GLOSSARY_KEY_PASSWORD_POLICY_ERROR_SEQUENCE
     *
     * @var string
     */
    public const ERROR_CUSTOMER_PASSWORD_SEQUENCE = 'customer.password.error.sequence';

    /**
     * @uses \Spryker\Zed\Customer\Business\CustomerPasswordPolicy\DenyListCustomerPasswordPolicy::GLOSSARY_KEY_PASSWORD_POLICY_ERROR_DENY_LIST
     *
     * @var string
     */
    public const ERROR_CUSTOMER_PASSWORD_DENY_LIST = 'customer.password.error.deny_list';

    public function addCustomerAlreadyExistsError(RestResponseInterface $restResponse): RestResponseInterface;

    public function addCustomerCantRegisterMessageError(RestResponseInterface $restResponse, string $errorMessage): RestResponseInterface;

    public function addCustomerNotFoundError(RestResponseInterface $restResponse): RestResponseInterface;

    public function addAddressNotFoundError(RestResponseInterface $restResponse): RestResponseInterface;

    public function addCustomerReferenceMissingError(RestResponseInterface $restResponse): RestResponseInterface;

    public function addPasswordsNotMatchError(RestResponseInterface $restResponse): RestResponseInterface;

    public function addPasswordChangeError(RestResponseInterface $restResponse, string $errorMessage): RestResponseInterface;

    public function addPasswordNotValidError(RestResponseInterface $restResponse): RestResponseInterface;

    public function addAddressNotSavedError(RestResponseInterface $restResponse): RestResponseInterface;

    public function addCustomerNotSavedError(RestResponseInterface $restResponse): RestResponseInterface;

    public function addCustomerUnauthorizedError(RestResponseInterface $restResponse): RestResponseInterface;

    public function addAddressUuidMissingError(RestResponseInterface $restResponse): RestResponseInterface;

    public function addNotAcceptedTermsError(RestResponseInterface $restResponse): RestResponseInterface;

    public function addNotValidGenderError(RestResponseInterface $restResponse): RestResponseInterface;

    public function addPasswordsDoNotMatchError(
        RestResponseInterface $restResponse,
        string $passwordFieldName,
        string $passwordConfirmFieldName
    ): RestResponseInterface;

    public function processCustomerErrorOnRegistration(
        RestResponseInterface $restResponse,
        CustomerResponseTransfer $customerResponseTransfer
    ): RestResponseInterface;

    public function processCustomerErrorOnUpdate(
        RestResponseInterface $restResponse,
        CustomerResponseTransfer $customerResponseTransfer
    ): RestResponseInterface;

    public function processCustomerErrorOnPasswordUpdate(
        RestResponseInterface $restResponse,
        CustomerResponseTransfer $customerResponseTransfer
    ): RestResponseInterface;

    public function processCustomerErrorOnPasswordReset(
        RestResponseInterface $restResponse,
        CustomerResponseTransfer $customerResponseTransfer
    ): RestResponseInterface;
}
