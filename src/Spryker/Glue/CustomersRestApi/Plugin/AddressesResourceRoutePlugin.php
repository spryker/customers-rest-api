<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\CustomersRestApi\Plugin;

use Generated\Shared\Transfer\RestAddressAttributesTransfer;
use Generated\Shared\Transfer\RouteAuthorizationConfigTransfer;
use Spryker\Glue\CustomersRestApi\CustomersRestApiConfig;
use Spryker\Glue\GlueApplicationAuthorizationConnectorExtension\Dependency\Plugin\DefaultAuthorizationStrategyAwareResourceRoutePluginInterface;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRouteCollectionInterface;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRoutePluginInterface;
use Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceWithParentPluginInterface;
use Spryker\Glue\Kernel\AbstractPlugin;

class AddressesResourceRoutePlugin extends AbstractPlugin implements ResourceRoutePluginInterface, ResourceWithParentPluginInterface, DefaultAuthorizationStrategyAwareResourceRoutePluginInterface
{
    /**
     * @uses \Spryker\Client\Customer\Plugin\Authorization\CustomerReferenceMatchingEntityIdAuthorizationStrategyPlugin::STRATEGY_NAME
     *
     * @var string
     */
    protected const STRATEGY_NAME = 'CustomerReferenceMatchingEntityId';

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRouteCollectionInterface $resourceRouteCollection
     *
     * @return \Spryker\Glue\GlueApplicationExtension\Dependency\Plugin\ResourceRouteCollectionInterface
     */
    public function configure(ResourceRouteCollectionInterface $resourceRouteCollection): ResourceRouteCollectionInterface
    {
        $resourceRouteCollection
            ->addGet('get', true)
            ->addPost('post', true)
            ->addPatch('patch', true)
            ->addDelete('delete', true);

        return $resourceRouteCollection;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return \Generated\Shared\Transfer\RouteAuthorizationConfigTransfer
     */
    public function getRouteAuthorizationDefaultConfiguration(): RouteAuthorizationConfigTransfer
    {
        $routeAuthorizationConfigTransfer = (new RouteAuthorizationConfigTransfer())
            ->setApiCode(CustomersRestApiConfig::RESPONSE_CODE_CUSTOMER_UNAUTHORIZED);

        // The check for `method_exists` added for BC reason only.
        if (!method_exists($routeAuthorizationConfigTransfer, 'addStrategy')) {
            return $this->setStrategy($routeAuthorizationConfigTransfer);
        }

        return $routeAuthorizationConfigTransfer->addStrategy(static::STRATEGY_NAME);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return string
     */
    public function getResourceType(): string
    {
        return CustomersRestApiConfig::RESOURCE_ADDRESSES;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return string
     */
    public function getController(): string
    {
        return 'address-resource';
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return string
     */
    public function getResourceAttributesClassName(): string
    {
        return RestAddressAttributesTransfer::class;
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @return string
     */
    public function getParentResourceType(): string
    {
        return CustomersRestApiConfig::RESOURCE_CUSTOMERS;
    }

    /**
     * @deprecated Will be removed without replacement.
     *
     * @param \Generated\Shared\Transfer\RouteAuthorizationConfigTransfer $routeAuthorizationConfigTransfer
     *
     * @return \Generated\Shared\Transfer\RouteAuthorizationConfigTransfer
     */
    protected function setStrategy(RouteAuthorizationConfigTransfer $routeAuthorizationConfigTransfer): RouteAuthorizationConfigTransfer
    {
        return $routeAuthorizationConfigTransfer->setStrategy(static::STRATEGY_NAME);
    }
}
