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

class CustomerForgottenPasswordStorefrontProcessor extends AbstractStorefrontProcessor
{
    public function __construct(
        protected CustomerClientInterface $customerClient,
    ) {
    }

    protected function processPost(mixed $data): mixed
    {
        $customerTransfer = (new CustomerTransfer())
            ->setEmail($data->getEmail());

        $this->customerClient->sendPasswordRestoreMail($customerTransfer);

        return null;
    }
}
