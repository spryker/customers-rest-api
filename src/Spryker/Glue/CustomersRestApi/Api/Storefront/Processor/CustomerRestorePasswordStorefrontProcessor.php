<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CustomersRestApi\Api\Storefront\Processor;

use Generated\Shared\Transfer\CustomerTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractStorefrontProcessor;
use Spryker\Client\Customer\CustomerClientInterface;
use Spryker\Glue\CustomersRestApi\Api\Storefront\Exception\CustomersExceptionFactory;

class CustomerRestorePasswordStorefrontProcessor extends AbstractStorefrontProcessor
{
    protected const string FIELD_PASSWORD = 'password';

    protected const string FIELD_CONFIRM_PASSWORD = 'confirmPassword';

    public function __construct(
        protected CustomerClientInterface $customerClient,
        protected CustomersExceptionFactory $exceptionFactory,
    ) {
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function processPatch(mixed $data): mixed
    {
        if ($data->getPassword() !== $data->getConfirmPassword()) {
            throw $this->exceptionFactory->createPasswordsDoNotMatchException(
                static::FIELD_PASSWORD,
                static::FIELD_CONFIRM_PASSWORD,
            );
        }

        $customerTransfer = (new CustomerTransfer())
            ->setRestorePasswordKey($data->getRestorePasswordKey())
            ->setPassword($data->getPassword());

        $customerResponseTransfer = $this->customerClient->restorePassword($customerTransfer);

        if ($customerResponseTransfer->getIsSuccess()) {
            return null;
        }

        $detail = null;
        if ($customerResponseTransfer->getErrors()->count() > 0) {
            $detail = $customerResponseTransfer->getErrors()->offsetGet(0)->getMessage();
        }

        throw $this->exceptionFactory->createRestorePasswordKeyInvalidException($detail);
    }
}
