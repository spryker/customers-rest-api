<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

declare(strict_types=1);

namespace Spryker\Glue\CustomersRestApi\Api\Storefront\Security;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Grants access when the authenticated customer's reference matches the
 * customerReference URI variable from the current request.
 *
 * Used in YAML security expressions:
 *   is_granted('CUSTOMER_OWNER')
 *
 * @extends \Symfony\Component\Security\Core\Authorization\Voter\Voter<string, mixed>
 */
class CustomerOwnershipVoter extends Voter
{
    protected const string ATTRIBUTE_CUSTOMER_OWNER = 'CUSTOMER_OWNER';

    protected const string URI_VARIABLE_CUSTOMER_REFERENCE = 'customerReference';

    protected const string USER_IDENTIFIER_CUSTOMER_REFERENCE_KEY = 'customer_reference';

    public function __construct(
        protected readonly RequestStack $requestStack,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $attribute === static::ATTRIBUTE_CUSTOMER_OWNER;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if ($user === null) {
            return false;
        }

        $authenticatedCustomerReference = $this->resolveAuthenticatedCustomerReference($user);

        if ($authenticatedCustomerReference === null) {
            return false;
        }

        $requestedCustomerReference = $this->resolveRequestedCustomerReference();

        if ($requestedCustomerReference === null) {
            return false;
        }

        return $authenticatedCustomerReference === $requestedCustomerReference;
    }

    /**
     * The user identifier for customer tokens is a JSON-encoded payload (built by
     * {@see \Generated\Shared\Transfer\CustomerIdentifierTransfer::toArray()}) that contains
     * the `customer_reference` claim.
     */
    protected function resolveAuthenticatedCustomerReference(UserInterface $user): ?string
    {
        $userData = json_decode($user->getUserIdentifier(), true);

        if (!is_array($userData) || !isset($userData[static::USER_IDENTIFIER_CUSTOMER_REFERENCE_KEY])) {
            return null;
        }

        return (string)$userData[static::USER_IDENTIFIER_CUSTOMER_REFERENCE_KEY];
    }

    /**
     * Reads the customer reference from Symfony's `_route_params`, which preserves
     * the original URL values set by the router. Cannot use `_api_uri_variables`
     * or `request.attributes['customerReference']` because CustomerRequestSubscriber
     * overwrites the latter with the authenticated customer's reference before
     * API Platform resolves URI variables from request attributes.
     */
    protected function resolveRequestedCustomerReference(): ?string
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request === null) {
            return null;
        }

        $routeParams = $request->attributes->get('_route_params', []);

        if (isset($routeParams[static::URI_VARIABLE_CUSTOMER_REFERENCE])) {
            return (string)$routeParams[static::URI_VARIABLE_CUSTOMER_REFERENCE];
        }

        return null;
    }
}
