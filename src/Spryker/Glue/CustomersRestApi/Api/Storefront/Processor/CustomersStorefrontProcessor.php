<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CustomersRestApi\Api\Storefront\Processor;

use Generated\Api\Storefront\CustomersStorefrontResource;
use Generated\Shared\Transfer\CustomerResponseTransfer;
use Generated\Shared\Transfer\CustomerTransfer;
use Generated\Shared\Transfer\RestUserTransfer;
use Spryker\ApiPlatform\State\Processor\AbstractStorefrontProcessor;
use Spryker\Client\Customer\CustomerClientInterface;
use Spryker\Glue\CustomersRestApi\Api\Storefront\Exception\CustomersExceptionFactory;
use Spryker\Glue\CustomersRestApi\Api\Storefront\Mapper\CustomersResourceMapperInterface;
use Spryker\Glue\GlueApplication\Rest\JsonApi\RestResource;
use Spryker\Glue\GlueApplication\Rest\Request\Data\Metadata;
use Spryker\Glue\GlueApplication\Rest\Request\Data\RestRequestInterface;
use Spryker\Glue\GlueApplication\Rest\Request\Data\Version;
use Spryker\Glue\GlueApplication\Rest\Request\RequestBuilder;
use Spryker\Service\Container\Attributes\Plugins;
use Spryker\Service\Serializer\SerializerServiceInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response;

class CustomersStorefrontProcessor extends AbstractStorefrontProcessor
{
    protected const string FIELD_PASSWORD = 'password';

    protected const string FIELD_CONFIRM_PASSWORD = 'confirmPassword';

    protected const string GLOSSARY_KEY_EMAIL_ALREADY_USED = 'customer.email.already.used';

    protected const string GLOSSARY_KEY_PASSWORD_SEQUENCE = 'customer.password.error.sequence';

    public function __construct(
        protected CustomerClientInterface $customerClient,
        protected SerializerServiceInterface $serializer,
        protected CustomersExceptionFactory $exceptionFactory,
        protected CustomersResourceMapperInterface $customersResourceMapper,
        /**
         * @var array<\Spryker\Glue\CustomersRestApiExtension\Dependency\Plugin\CustomerPostCreatePluginInterface>
         */
        #[Plugins(dependencyProviderMethod: 'getCustomerPostCreatePlugins')]
        protected array $customerPostCreatePlugins = [],
    ) {
    }

    protected function processPost(mixed $data): mixed
    {
        return $this->registerCustomer($data);
    }

    protected function processPatch(mixed $data): mixed
    {
        return $this->updateCustomer($data);
    }

    protected function processDelete(): mixed
    {
        return $this->anonymizeCustomer();
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function registerCustomer(CustomersStorefrontResource $resource): CustomersStorefrontResource
    {
        if (!$resource->acceptedTerms) {
            throw $this->exceptionFactory->createNotAcceptedTermsException();
        }

        $this->assertPasswordsMatch($resource->password, $resource->confirmPassword);

        $customerTransfer = $this->customersResourceMapper->mapResourceToCustomerTransfer($resource, new CustomerTransfer());
        $customerResponseTransfer = $this->customerClient->registerCustomer($customerTransfer);

        if (!$customerResponseTransfer->getIsSuccess()) {
            $this->handleRegistrationError($customerResponseTransfer);
        }

        $this->executeCustomerPostCreatePlugins($customerResponseTransfer->getCustomerTransfer());

        return $this->serializer->denormalize(
            $this->customersResourceMapper->mapCustomerTransferToResourceData($customerResponseTransfer->getCustomerTransfer()),
            CustomersStorefrontResource::class,
        );
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function updateCustomer(CustomersStorefrontResource $resource): CustomersStorefrontResource
    {
        if (!$this->hasCustomer()) {
            throw $this->exceptionFactory->createCustomerUnauthorizedException(Response::HTTP_UNAUTHORIZED);
        }

        $uriVariables = $this->getUriVariables();
        $customerReference = $uriVariables['customerReference'] ?? null;

        if ($customerReference === null || $customerReference !== $this->getCustomerReference()) {
            throw $this->exceptionFactory->createCustomerUnauthorizedException();
        }

        $existingCustomerResponse = $this->customerClient->findCustomerByReference(
            (new CustomerTransfer())->setCustomerReference($customerReference),
        );

        if (!$existingCustomerResponse->getHasCustomer()) {
            throw $this->exceptionFactory->createCustomerNotFoundException();
        }

        $existingCustomer = $existingCustomerResponse->getCustomerTransfer();

        if ($resource->password !== null || $resource->confirmPassword !== null) {
            $this->assertPasswordsMatch($resource->password, $resource->confirmPassword);
        }

        // Password changes are handled by the /customer-password endpoint, not PATCH /customers
        $customerTransfer = $this->customersResourceMapper->mapResourceToCustomerTransfer($resource, new CustomerTransfer(), false);
        $customerTransfer->setCustomerReference($customerReference);
        $customerTransfer->setIdCustomer($existingCustomer->getIdCustomer());

        $customerResponseTransfer = $this->customerClient->updateCustomer($customerTransfer);

        if (!$customerResponseTransfer->getIsSuccess()) {
            $this->handleUpdateError($customerResponseTransfer);
        }

        return $this->serializer->denormalize(
            $this->customersResourceMapper->mapCustomerTransferToResourceData($customerResponseTransfer->getCustomerTransfer()),
            CustomersStorefrontResource::class,
        );
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function anonymizeCustomer(): null
    {
        if (!$this->hasCustomer()) {
            throw $this->exceptionFactory->createCustomerUnauthorizedException(Response::HTTP_UNAUTHORIZED);
        }

        $uriVariables = $this->getUriVariables();

        $customerTransfer = (new CustomerTransfer())->setCustomerReference($uriVariables['customerReference'] ?? $this->getCustomerReference());
        $this->customerClient->anonymizeCustomer($customerTransfer);

        return null;
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function assertPasswordsMatch(?string $password, ?string $confirmPassword): void
    {
        if ($password !== $confirmPassword) {
            throw $this->exceptionFactory->createPasswordsDoNotMatchException(
                static::FIELD_PASSWORD,
                static::FIELD_CONFIRM_PASSWORD,
            );
        }
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function handleRegistrationError(CustomerResponseTransfer $customerResponseTransfer): void
    {
        foreach ($customerResponseTransfer->getErrors() as $customerErrorTransfer) {
            if ($customerErrorTransfer->getMessage() === static::GLOSSARY_KEY_PASSWORD_SEQUENCE) {
                throw $this->exceptionFactory->createPasswordSequenceNotAllowedException();
            }

            if ($customerErrorTransfer->getMessage() === static::GLOSSARY_KEY_EMAIL_ALREADY_USED) {
                throw $this->exceptionFactory->createCustomerAlreadyExistsException();
            }
        }

        throw $this->exceptionFactory->createCustomerFailedToSaveException();
    }

    /**
     * @throws \Spryker\ApiPlatform\Exception\GlueApiException
     */
    protected function handleUpdateError(CustomerResponseTransfer $customerResponseTransfer): void
    {
        foreach ($customerResponseTransfer->getErrors() as $customerErrorTransfer) {
            if ($customerErrorTransfer->getMessage() === static::GLOSSARY_KEY_EMAIL_ALREADY_USED) {
                throw $this->exceptionFactory->createCustomerAlreadyExistsException();
            }
        }

        throw $this->exceptionFactory->createCustomerFailedToSaveException();
    }

    protected function executeCustomerPostCreatePlugins(CustomerTransfer $customerTransfer): void
    {
        if ($this->customerPostCreatePlugins === []) {
            return;
        }

        $restRequest = $this->buildRestRequestForLegacyPlugins();

        foreach ($this->customerPostCreatePlugins as $plugin) {
            $plugin->postCreate($restRequest, $customerTransfer);
        }
    }

    /**
     * Legacy CustomerPostCreatePluginInterface::postCreate() expects a RestRequestInterface
     * built by the legacy Glue REST stack. In the API Platform flow that object is not
     * constructed, so we build a minimal adapter wrapping the Symfony request, populating
     * RestUser from the `X-Anonymous-Customer-Unique-Id` header so guest-cart migration
     * plugins keep working.
     */
    protected function buildRestRequestForLegacyPlugins(): RestRequestInterface
    {
        $httpRequest = $this->hasRequest() ? $this->getRequest() : new SymfonyRequest();

        $metadata = new Metadata('json', 'json', $httpRequest->getMethod(), 'DE', true, new Version(1, 1));

        $restRequest = (new RequestBuilder(new RestResource('customers', null)))
            ->addHttpRequest($httpRequest)
            ->addMetadata($metadata)
            ->build();

        $anonymousCustomerId = $httpRequest->headers->get('X-Anonymous-Customer-Unique-Id');

        if ($anonymousCustomerId !== null && $anonymousCustomerId !== '') {
            $restRequest->setRestUser(
                (new RestUserTransfer())->setNaturalIdentifier($anonymousCustomerId),
            );
        }

        return $restRequest;
    }
}
