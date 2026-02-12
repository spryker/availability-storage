<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Client\AvailabilityStorage\Expander;

use Generated\Shared\Transfer\ProductAbstractAvailabilityTransfer;
use Generated\Shared\Transfer\ProductConcreteAvailabilityTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Spryker\Client\AvailabilityStorage\Storage\AvailabilityStorageReaderInterface;

class ProductViewAvailabilityExpander implements ProductViewAvailabilityExpanderInterface
{
    /**
     * @var \Spryker\Client\AvailabilityStorage\Storage\AvailabilityStorageReaderInterface
     */
    protected $availabilityStorageReader;

    /**
     * @var array<\Spryker\Client\AvailabilityStorageExtension\Dependency\Plugin\AvailabilityStorageStrategyPluginInterface>
     */
    protected $availabilityStorageStrategyPlugins;

    /**
     * @param \Spryker\Client\AvailabilityStorage\Storage\AvailabilityStorageReaderInterface $availabilityStorageReader
     * @param array<\Spryker\Client\AvailabilityStorageExtension\Dependency\Plugin\AvailabilityStorageStrategyPluginInterface> $availabilityStorageStrategyPlugins
     */
    public function __construct(
        AvailabilityStorageReaderInterface $availabilityStorageReader,
        array $availabilityStorageStrategyPlugins
    ) {
        $this->availabilityStorageReader = $availabilityStorageReader;
        $this->availabilityStorageStrategyPlugins = $availabilityStorageStrategyPlugins;
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return \Generated\Shared\Transfer\ProductViewTransfer
     */
    public function expandProductViewWithAvailability(ProductViewTransfer $productViewTransfer): ProductViewTransfer
    {
        if (!$productViewTransfer->getIdProductAbstract()) {
            $productViewTransfer->setAvailable(false);

            return $productViewTransfer;
        }

        $productAbstractAvailabilityTransfer = $this->availabilityStorageReader
            ->findAbstractProductAvailability($productViewTransfer->getIdProductAbstractOrFail());

        if ($productAbstractAvailabilityTransfer === null) {
            $productViewTransfer->setAvailable(false);

            return $productViewTransfer;
        }

        if (!$productViewTransfer->getIdProductConcrete()) {
            return $productViewTransfer->setAvailable($productAbstractAvailabilityTransfer->getAvailabilityOrFail()->greaterThan(0));
        }

        $productViewTransfer = $this->expandProductViewTransferWithConcreteAvailability($productViewTransfer, $productAbstractAvailabilityTransfer);

        return $this->executeAvailabilityStorageStrategyPlugins($productViewTransfer);
    }

    /**
     * @param \Generated\Shared\Transfer\ProductViewTransfer $productViewTransfer
     *
     * @return \Generated\Shared\Transfer\ProductViewTransfer
     */
    protected function executeAvailabilityStorageStrategyPlugins(ProductViewTransfer $productViewTransfer): ProductViewTransfer
    {
        foreach ($this->availabilityStorageStrategyPlugins as $availabilityStorageStrategyPlugin) {
            if ($availabilityStorageStrategyPlugin->isApplicable($productViewTransfer)) {
                $productViewTransfer->setAvailable(
                    $availabilityStorageStrategyPlugin->isProductAvailable($productViewTransfer),
                );

                break;
            }
        }

        return $productViewTransfer;
    }

    protected function expandProductViewTransferWithConcreteAvailability(
        ProductViewTransfer $productViewTransfer,
        ProductAbstractAvailabilityTransfer $productAbstractAvailabilityTransfer
    ): ProductViewTransfer {
        $productConcreteAvailabilityTransfer = $this->findProductConcreteAvailabilityTransfer(
            $productViewTransfer,
            $productAbstractAvailabilityTransfer,
        );

        if ($productConcreteAvailabilityTransfer === null) {
            return $productViewTransfer;
        }

        $availability = $productConcreteAvailabilityTransfer->getAvailability();
        $isNeverOutOfStock = $productConcreteAvailabilityTransfer->getIsNeverOutOfStock() ?? false;

        return $productViewTransfer->setAvailable($isNeverOutOfStock || $availability?->greaterThan(0))
            ->setIsNeverOutOfStock($isNeverOutOfStock)
            ->setStockQuantity($availability?->toFloat());
    }

    protected function findProductConcreteAvailabilityTransfer(
        ProductViewTransfer $productViewTransfer,
        ProductAbstractAvailabilityTransfer $productAbstractAvailabilityTransfer
    ): ?ProductConcreteAvailabilityTransfer {
        foreach ($productAbstractAvailabilityTransfer->getProductConcreteAvailabilities() as $productConcreteAvailabilityTransfer) {
            if ($productConcreteAvailabilityTransfer->getSku() === $productViewTransfer->getSku()) {
                return $productConcreteAvailabilityTransfer;
            }
        }

        return null;
    }
}
