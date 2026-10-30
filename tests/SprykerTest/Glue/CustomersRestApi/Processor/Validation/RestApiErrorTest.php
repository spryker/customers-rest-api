<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Glue\CustomersRestApi\Processor\Validation;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\CustomerErrorTransfer;
use Generated\Shared\Transfer\CustomerResponseTransfer;
use Spryker\Glue\CustomersRestApi\CustomersRestApiConfig;
use Spryker\Glue\CustomersRestApi\Processor\Validation\RestApiError;
use Spryker\Glue\CustomersRestApi\Processor\Validation\RestApiErrorInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Glue
 * @group CustomersRestApi
 * @group Processor
 * @group Validation
 * @group RestApiErrorTest
 * Add your own group annotations below this line
 */
class RestApiErrorTest extends Unit
{
    public function testProcessCustomerErrorOnPasswordUpdateAddsTooManyRequestsErrorWhenPasswordChangeLimitIsExceeded(): void
    {
        // Arrange
        $customerResponseTransfer = (new CustomerResponseTransfer())
            ->setIsSuccess(false)
            ->addError((new CustomerErrorTransfer())->setMessage(RestApiErrorInterface::ERROR_CUSTOMER_PASSWORD_CHANGE_LIMIT_EXCEEDED));

        // Act
        $restResponse = (new RestApiError())->processCustomerErrorOnPasswordUpdate(new RestResponse(), $customerResponseTransfer);

        // Assert
        $this->assertCount(1, $restResponse->getErrors());
        $this->assertSame(CustomersRestApiConfig::RESPONSE_CODE_CUSTOMER_PASSWORD_CHANGE_LIMIT_EXCEEDED, $restResponse->getErrors()[0]->getCode());
        $this->assertSame(Response::HTTP_TOO_MANY_REQUESTS, $restResponse->getErrors()[0]->getStatus());
        $this->assertSame(CustomersRestApiConfig::RESPONSE_MESSAGE_CUSTOMER_PASSWORD_CHANGE_LIMIT_EXCEEDED, $restResponse->getErrors()[0]->getDetail());
    }
}
