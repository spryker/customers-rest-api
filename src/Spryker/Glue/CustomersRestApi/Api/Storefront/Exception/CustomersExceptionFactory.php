<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CustomersRestApi\Api\Storefront\Exception;

use Generated\Shared\Transfer\CustomerResponseTransfer;
use Generated\Shared\Transfer\RestErrorMessageTransfer;
use Spryker\ApiPlatform\Exception\GlueApiException;
use Spryker\Glue\CustomersRestApi\CustomersRestApiConfig;
use Symfony\Component\HttpFoundation\Response;

/**
 * Builds pre-configured `GlueApiException` instances for known customer error scenarios.
 *
 * Centralises the mapping of legacy Spryker Glue error codes (defined as public constants in
 * {@see CustomersRestApiConfig}) onto API Platform exceptions. Replaces the per-processor
 * if/else error-construction noise that mirrored the legacy {@see RestApiError} +
 * {@see RestApiValidator} pair.
 */
class CustomersExceptionFactory
{
    public function __construct(
        protected CustomersRestApiConfig $customersRestApiConfig,
    ) {
    }

    public function createCustomerNotFoundException(int $httpStatus = Response::HTTP_NOT_FOUND): GlueApiException
    {
        return new GlueApiException(
            $httpStatus,
            CustomersRestApiConfig::RESPONSE_CODE_CUSTOMER_NOT_FOUND,
            CustomersRestApiConfig::RESPONSE_DETAILS_CUSTOMER_NOT_FOUND,
        );
    }

    public function createCustomerUnauthorizedException(int $httpStatus = Response::HTTP_FORBIDDEN): GlueApiException
    {
        return new GlueApiException(
            $httpStatus,
            CustomersRestApiConfig::RESPONSE_CODE_CUSTOMER_UNAUTHORIZED,
            CustomersRestApiConfig::RESPONSE_DETAILS_CUSTOMER_UNAUTHORIZED,
        );
    }

    public function createPasswordsDoNotMatchException(string $passwordField, string $confirmPasswordField): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomersRestApiConfig::RESPONSE_CODE_PASSWORDS_DONT_MATCH,
            sprintf(
                CustomersRestApiConfig::RESPONSE_DETAILS_PASSWORDS_DONT_MATCH,
                $passwordField,
                $confirmPasswordField,
            ),
        );
    }

    public function createNotAcceptedTermsException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            CustomersRestApiConfig::RESPONSE_CODE_NOT_ACCEPTED_TERMS,
            CustomersRestApiConfig::RESPONSE_DETAILS_NOT_ACCEPTED_TERMS,
        );
    }

    public function createCustomerAlreadyExistsException(?string $detail = null): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomersRestApiConfig::RESPONSE_CODE_CUSTOMER_ALREADY_EXISTS,
            $detail ?? CustomersRestApiConfig::RESPONSE_MESSAGE_CUSTOMER_ALREADY_EXISTS,
        );
    }

    public function createPasswordSequenceNotAllowedException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            CustomersRestApiConfig::RESPONSE_CODE_CUSTOMER_PASSWORD_SEQUENCE_NOT_ALLOWED,
            CustomersRestApiConfig::RESPONSE_MESSAGE_CUSTOMER_PASSWORD_SEQUENCE_NOT_ALLOWED,
        );
    }

    public function createInvalidPasswordException(): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_BAD_REQUEST,
            CustomersRestApiConfig::RESPONSE_CODE_INVALID_PASSWORD,
            CustomersRestApiConfig::RESPONSE_DETAILS_INVALID_PASSWORD,
        );
    }

    public function createCustomerFailedToSaveException(?string $detail = null): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomersRestApiConfig::RESPONSE_CODE_CUSTOMER_FAILED_TO_SAVE,
            $detail ?? CustomersRestApiConfig::RESPONSE_DETAILS_CUSTOMER_FAILED_TO_SAVE,
        );
    }

    public function createAddressNotFoundException(int $httpStatus = Response::HTTP_NOT_FOUND): GlueApiException
    {
        return new GlueApiException(
            $httpStatus,
            CustomersRestApiConfig::RESPONSE_CODE_ADDRESS_NOT_FOUND,
            CustomersRestApiConfig::RESPONSE_DETAILS_ADDRESS_NOT_FOUND,
        );
    }

    public function createRestorePasswordKeyInvalidException(?string $detail = null): GlueApiException
    {
        return new GlueApiException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            CustomersRestApiConfig::RESPONSE_CODE_RESTORE_PASSWORD_KEY_INVALID,
            $detail ?? CustomersRestApiConfig::RESPONSE_DETAILS_RESTORE_PASSWORD_KEY_INVALID,
        );
    }

    /**
     * Iterates {@see CustomerResponseTransfer::getErrors()} and maps each `getMessage()` glossary
     * key through {@see CustomersRestApiConfig::getErrorMapping()}. Recognised errors are emitted
     * as a multi-error JSON:API response (via {@see GlueApiException::setErrors()}). When no error
     * matches the mapping, falls back to the supplied code/detail/status.
     */
    public function createExceptionFromCustomerResponse(
        CustomerResponseTransfer $customerResponseTransfer,
        string $fallbackCode,
        string $fallbackDetail,
        int $fallbackStatus = Response::HTTP_UNPROCESSABLE_ENTITY,
    ): GlueApiException {
        $errors = $this->mapResponseErrors($customerResponseTransfer);

        if ($errors === []) {
            $errors[] = [
                RestErrorMessageTransfer::CODE => $fallbackCode,
                RestErrorMessageTransfer::STATUS => $fallbackStatus,
                RestErrorMessageTransfer::DETAIL => $fallbackDetail,
            ];
        }

        $firstError = $errors[0];
        $exception = new GlueApiException(
            (int)$firstError[RestErrorMessageTransfer::STATUS],
            (string)$firstError[RestErrorMessageTransfer::CODE],
            (string)$firstError[RestErrorMessageTransfer::DETAIL],
        );

        if (count($errors) > 1) {
            $exception->setErrors($errors);
        }

        return $exception;
    }

    /**
     * @return array<int, array{code: string, status: int, detail: string}>
     */
    public function mapResponseErrors(CustomerResponseTransfer $customerResponseTransfer): array
    {
        $errorMapping = $this->customersRestApiConfig->getErrorMapping();
        $errors = [];

        foreach ($customerResponseTransfer->getErrors() as $customerErrorTransfer) {
            $message = $customerErrorTransfer->getMessage();
            if ($message === null || !isset($errorMapping[$message])) {
                continue;
            }

            $mappedError = $errorMapping[$message];

            $errors[] = [
                RestErrorMessageTransfer::CODE => (string)$mappedError[RestErrorMessageTransfer::CODE],
                RestErrorMessageTransfer::STATUS => (int)$mappedError[RestErrorMessageTransfer::STATUS],
                RestErrorMessageTransfer::DETAIL => (string)$mappedError[RestErrorMessageTransfer::DETAIL],
            ];
        }

        return $errors;
    }
}
