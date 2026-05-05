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

class CustomerPasswordStorefrontProcessor extends AbstractStorefrontProcessor
{
    protected const string FIELD_NEW_PASSWORD = 'newPassword';

    protected const string FIELD_CONFIRM_PASSWORD = 'confirmPassword';

    protected const string GLOSSARY_KEY_PASSWORD_SEQUENCE = 'customer.password.error.sequence';

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
        $this->assertCustomerOwnership();

        if ($data->getNewPassword() !== $data->getConfirmPassword()) {
            throw $this->exceptionFactory->createPasswordsDoNotMatchException(
                static::FIELD_NEW_PASSWORD,
                static::FIELD_CONFIRM_PASSWORD,
            );
        }

        $customerReference = $this->getUriVariables()['customerReference'];

        $customerResponseTransfer = $this->customerClient->findCustomerByReference(
            (new CustomerTransfer())->setCustomerReference($customerReference),
        );

        if (!$customerResponseTransfer->getHasCustomer()) {
            throw $this->exceptionFactory->createCustomerNotFoundException();
        }

        $customerTransfer = $customerResponseTransfer->getCustomerTransfer();
        $customerTransfer->setPassword($data->getPassword());
        $customerTransfer->setNewPassword($data->getNewPassword());

        $customerResponseTransfer = $this->customerClient->updateCustomerPassword($customerTransfer);

        if ($customerResponseTransfer->getIsSuccess()) {
            return null;
        }

        foreach ($customerResponseTransfer->getErrors() as $customerErrorTransfer) {
            if ($customerErrorTransfer->getMessage() === static::GLOSSARY_KEY_PASSWORD_SEQUENCE) {
                throw $this->exceptionFactory->createPasswordSequenceNotAllowedException();
            }
        }

        throw $this->exceptionFactory->createInvalidPasswordException();
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function assertCustomerOwnership(): void
    {
        if (!$this->hasCustomer()) {
            throw $this->exceptionFactory->createCustomerUnauthorizedException();
        }

        $authenticatedReference = $this->getCustomerReference();
        $request = $this->getRequest();
        $routeParams = $request->attributes->get('_route_params', []);
        $requestedReference = $routeParams['customerReference'] ?? $this->getUriVariables()['customerReference'] ?? null;

        if ($requestedReference !== $authenticatedReference) {
            throw $this->exceptionFactory->createCustomerUnauthorizedException();
        }
    }
}
