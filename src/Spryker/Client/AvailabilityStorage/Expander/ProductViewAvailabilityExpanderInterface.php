<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AvailabilityStorage\Expander;

use Generated\Shared\Transfer\ProductViewTransfer;

interface ProductViewAvailabilityExpanderInterface
{
    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return \Generated\Shared\Transfer\ProductViewTransfer
     */
    public function expandProductViewWithAvailability(ProductViewTransfer $productViewTransfer): ProductViewTransfer;
}
