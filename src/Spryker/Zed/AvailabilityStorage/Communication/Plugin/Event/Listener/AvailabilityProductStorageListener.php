<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AvailabilityStorage\Communication\Plugin\Event\Listener;

use Orm\Zed\Product\Persistence\Map\SpyProductTableMap;
use Orm\Zed\Product\Persistence\SpyProductAbstract;
use Spryker\Zed\Event\Dependency\Plugin\EventBulkHandlerInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Zed\Product\Dependency\ProductEvents;

/**
 * @method \Spryker\Zed\AvailabilityStorage\Persistence\AvailabilityStorageQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\AvailabilityStorage\Communication\AvailabilityStorageCommunicationFactory getFactory()
 * @method \Spryker\Zed\AvailabilityStorage\Business\AvailabilityStorageFacadeInterface getFacade()
 * @method \Spryker\Zed\AvailabilityStorage\AvailabilityStorageConfig getConfig()
 */
class AvailabilityProductStorageListener extends AbstractPlugin implements EventBulkHandlerInterface
{
    /**
     * @var string
     */
    public const FK_PRODUCT_ABSTRACT = 'fkProductAbstract';

    /**
     * @api
     *
     * @param array<\Generated\Shared\Transfer\EventEntityTransfer> $eventEntityTransfers
     * @param string $eventName
     *
     * @return void
     */
    public function handleBulk(array $eventEntityTransfers, $eventName)
    {
        $abstractProductSkus = [];
        $abstractProductIds = [];

        if ($eventName !== ProductEvents::ENTITY_SPY_PRODUCT_UPDATE) {
            return;
        }

        $spyAbstractProducts = $this->findProductAbstracts($eventEntityTransfers);
        foreach ($spyAbstractProducts as $spyAbstractProduct) {
            if ($this->hasActiveProducts($spyAbstractProduct)) {
                $abstractProductSkus[] = $spyAbstractProduct->getSku();
            } else {
                $abstractProductIds[] = $spyAbstractProduct->getIdProductAbstract();
            }
        }

        $abstractAvailabilityIds = $this->findAvailabilityAbstractBySkus($abstractProductSkus);
        $this->getFacade()->publish($abstractAvailabilityIds);
        $this->unpublishByAbstractProductIds($abstractProductIds);
    }

    /**
     * @param array $idAbstractProducts
     *
     * @return void
     */
    protected function unpublishByAbstractProductIds(array $idAbstractProducts)
    {
        $spyAvailabilityStorageEntities = $this->findAvailabilityStorageEntitiesByAbstractProductIds($idAbstractProducts);
        foreach ($spyAvailabilityStorageEntities as $spyAvailabilityStorageEntity) {
            $spyAvailabilityStorageEntity->setIsSendingToQueue($this->getConfig()->isSendingToQueue());
            $spyAvailabilityStorageEntity->delete();
        }
    }

    /**
     * @param \Orm\Zed\Product\Persistence\SpyProductAbstract $spyAbstractProducts
     *
     * @return bool
     */
    protected function hasActiveProducts(SpyProductAbstract $spyAbstractProducts)
    {
        foreach ($spyAbstractProducts->getSpyProducts() as $spyProduct) {
            if ($spyProduct->getIsActive()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param array $eventTransfers
     *
     * @return array<\Orm\Zed\Product\Persistence\SpyProductAbstract>
     */
    public function findProductAbstracts(array $eventTransfers)
    {
        $abstractProductIds = $this->getFactory()->getEventBehaviorFacade()->getEventTransferForeignKeys($eventTransfers, SpyProductTableMap::COL_FK_PRODUCT_ABSTRACT);

        return $this->getQueryContainer()->queryProductAbstractWithProductByAbstractProductIds($abstractProductIds)->find()->getData();
    }

    /**
     * @param array $abstractProductSkus
     *
     * @return array<\Orm\Zed\Availability\Persistence\SpyAvailabilityAbstract>
     */
    protected function findAvailabilityAbstractBySkus(array $abstractProductSkus)
    {
        return $this->getQueryContainer()->queryAvailabilityAbstractByAbstractProductSkus($abstractProductSkus)->find()->getData();
    }

    /**
     * @param array $abstractProductIds
     *
     * @return array
     */
    protected function findAvailabilityStorageEntitiesByAbstractProductIds(array $abstractProductIds)
    {
        return $this->getQueryContainer()->queryAvailabilityStorageByProductAbstractIds($abstractProductIds)->find()->toKeyIndex(static::FK_PRODUCT_ABSTRACT);
    }
}
