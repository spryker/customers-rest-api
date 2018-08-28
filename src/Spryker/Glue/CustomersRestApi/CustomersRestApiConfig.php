<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CustomersRestApi;

use Spryker\Glue\Kernel\AbstractBundleConfig;

class CustomersRestApiConfig extends AbstractBundleConfig
{
    public const RESOURCE_CUSTOMERS = 'customers';

    public const RESPONSE_CODE_CUSTOMER_ALREADY_EXISTS = '400';
    public const RESPONSE_CODE_CUSTOMER_CANT_REGISTER_CUSTOMER = '401';

    public const RESPONSE_MESSAGE_CUSTOMER_ALREADY_EXISTS = 'Customer with this email already exists.';
    public const RESPONSE_MESSAGE_CUSTOMER_CANT_REGISTER_CUSTOMER = 'Can`t register a customer.';

    public const RESPONSE_CODE_CUSTOMER_NOT_FOUND = '402';
    public const RESPONSE_DETAILS_CUSTOMER_NOT_FOUND = 'Customer not found.';

    public const RESOURCE_ADDRESSES = 'addresses';

    public const RESPONSE_CODE_CUSTOMER_ADDRESSES_NOT_FOUND = '403';
    public const RESPONSE_DETAILS_CUSTOMER_ADDRESSES_NOT_FOUND = 'Customer does not have addresses.';

    public const RESPONSE_CODE_ADDRESS_NOT_FOUND = '404';
    public const RESPONSE_DETAILS_ADDRESS_NOT_FOUND = 'Address was not found.';

    public const RESPONSE_CODE_CUSTOMER_REFERENCE_MISSING = '405';
    public const RESPONSE_DETAILS_CUSTOMER_REFERENCE_MISSING = 'Customer reference is missing.';

    public const RESOURCE_CUSTOMER_PASSWORD = 'customer-password';

    public const RESPONSE_CODE_PASSWORDS_DONT_MATCH = '406';
    public const RESPONSE_DETAILS_PASSWORDS_DONT_MATCH = 'Passwords don\'t match.';
    public const RESPONSE_CODE_PASSWORD_CHANGE_FAILED = '407';
    public const RESPONSE_CODE_INVALID_PASSWORD = '408';
    public const RESPONSE_DETAILS_INVALID_PASSWORD = 'Invalid password';

    public const RESPONSE_CODE_CUSTOMER_ADDRESS_FAILED_TO_SAVE = '409';
    public const RESPONSE_DETAILS_CUSTOMER_ADDRESS_FAILED_TO_SAVE = 'Failed to save customer address.';
}
