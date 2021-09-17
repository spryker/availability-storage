<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AvailabilityStorage\Persistence;

interface AvailabilityStorageRepositoryInterface
{
    /**
     * @param array<int> $productAbstractIds
     *
     * @return array<int>
     */
    public function getAvailabilityAbstractIdsByProductAbstractIds(array $productAbstractIds): array;
}
