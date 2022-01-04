<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AvailabilityStorage\Business\Storage;

use Orm\Zed\AvailabilityStorage\Persistence\SpyAvailabilityStorage;
use Spryker\Zed\AvailabilityStorage\Persistence\AvailabilityStorageQueryContainerInterface;
use Spryker\Zed\AvailabilityStorage\Persistence\AvailabilityStorageRepositoryInterface;

class AvailabilityStorage implements AvailabilityStorageInterface
{
    /**
     * @var string
     */
    public const ID_PRODUCT_ABSTRACT = 'id_product_abstract';

    /**
     * @var string
     */
    public const ID_AVAILABILITY_ABSTRACT = 'id_availability_abstract';

    /**
     * @var string
     */
    public const FK_AVAILABILITY_ABSTRACT = 'fkAvailabilityAbstract';

    /**
     * @var string
     */
    public const STORE = 'Store';

    /**
     * @var string
     */
    public const STORE_NAME = 'name';

    /**
     * @var \Spryker\Zed\AvailabilityStorage\Persistence\AvailabilityStorageQueryContainerInterface
     */
    protected $queryContainer;

    /**
     * @deprecated Use {@link \Spryker\Zed\SynchronizationBehavior\SynchronizationBehaviorConfig::isSynchronizationEnabled()} instead.
     *
     * @var bool
     */
    protected $isSendingToQueue = true;

    /**
     * @var \Spryker\Zed\AvailabilityStorage\Persistence\AvailabilityStorageRepositoryInterface
     */
    protected $availabilityStorageRepository;

    /**
     * @param \Spryker\Zed\AvailabilityStorage\Persistence\AvailabilityStorageQueryContainerInterface $queryContainer
     * @param bool $isSendingToQueue
     * @param \Spryker\Zed\AvailabilityStorage\Persistence\AvailabilityStorageRepositoryInterface $availabilityStorageRepository
     */
    public function __construct(
        AvailabilityStorageQueryContainerInterface $queryContainer,
        $isSendingToQueue,
        AvailabilityStorageRepositoryInterface $availabilityStorageRepository
    ) {
        $this->queryContainer = $queryContainer;
        $this->isSendingToQueue = $isSendingToQueue;
        $this->availabilityStorageRepository = $availabilityStorageRepository;
    }

    /**
     * @param array $availabilityIds
     *
     * @return void
     */
    public function publish(array $availabilityIds)
    {
        $availabilityEntityCollection = $this->findAvailabilityAbstractEntities($availabilityIds);
        $availabilityStorageEntityCollection = $this->findAvailabilityStorageEntitiesByAvailabilityAbstractIds($availabilityIds);

        $this->storeData($availabilityEntityCollection, $availabilityStorageEntityCollection);
    }

    /**
     * @param array $availabilityIds
     *
     * @return void
     */
    public function unpublish(array $availabilityIds)
    {
        $availabilityStorageEntityCollection = $this->findAvailabilityStorageEntitiesByAvailabilityAbstractIds($availabilityIds);
        foreach ($availabilityStorageEntityCollection as $availabilityStorageEntity) {
            $availabilityStorageEntity->delete();
        }
    }

    /**
     * @param array<int> $productAbstractIds
     *
     * @return void
     */
    public function publishByProductAbstractIds(array $productAbstractIds): void
    {
        $availabilityAbstractIds = $this->availabilityStorageRepository->getAvailabilityAbstractIdsByProductAbstractIds($productAbstractIds);
        $this->publish($availabilityAbstractIds);
    }

    /**
     * @param array<int> $productAbstractIds
     *
     * @return void
     */
    public function unpublishByProductAbstractIds(array $productAbstractIds): void
    {
        $availabilityAbstractIds = $this->availabilityStorageRepository->getAvailabilityAbstractIdsByProductAbstractIds($productAbstractIds);
        $this->unpublish($availabilityAbstractIds);
    }

    /**
     * @param array $availabilityEntities
     * @param array $availabilityStorageEntityCollection
     *
     * @return void
     */
    protected function storeData(array $availabilityEntities, array $availabilityStorageEntityCollection)
    {
        foreach ($availabilityEntities as $availability) {
            $idAvailability = $availability[static::ID_AVAILABILITY_ABSTRACT];
            $storeName = $availability[static::STORE][static::STORE_NAME];

            if ($this->isExistingEntity($availabilityStorageEntityCollection, $idAvailability, $storeName)) {
                $this->storeDataSet($availability, $availabilityStorageEntityCollection[$idAvailability]);

                continue;
            }

            $this->storeDataSet($availability);
        }
    }

    /**
     * @param array $availability
     * @param \Orm\Zed\AvailabilityStorage\Persistence\SpyAvailabilityStorage|null $availabilityStorageEntity
     *
     * @return void
     */
    protected function storeDataSet(array $availability, ?SpyAvailabilityStorage $availabilityStorageEntity = null)
    {
        if ($availabilityStorageEntity === null) {
            $availabilityStorageEntity = new SpyAvailabilityStorage();
        }

        $storeName = $availability[static::STORE][static::STORE_NAME];
        $availabilityStorageEntity->setFkProductAbstract($availability[static::ID_PRODUCT_ABSTRACT])
            ->setFkAvailabilityAbstract($availability[static::ID_AVAILABILITY_ABSTRACT])
            ->setData($availability)
            ->setStore($storeName)
            ->setIsSendingToQueue($this->isSendingToQueue);

        $availabilityStorageEntity->save();
    }

    /**
     * @param array $availabilityIds
     *
     * @return array
     */
    protected function findAvailabilityAbstractEntities(array $availabilityIds)
    {
        return $this->queryContainer
            ->queryAvailabilityAbstractWithRelationsByIds($availabilityIds)
            ->find()
            ->getData();
    }

    /**
     * @param array $availabilityAbstractIds
     *
     * @return array<\Orm\Zed\AvailabilityStorage\Persistence\SpyAvailabilityStorage>
     */
    protected function findAvailabilityStorageEntitiesByAvailabilityAbstractIds(array $availabilityAbstractIds)
    {
        return $this->queryContainer
            ->queryAvailabilityStorageByAvailabilityAbstractIds($availabilityAbstractIds)
            ->find()
            ->toKeyIndex(static::FK_AVAILABILITY_ABSTRACT);
    }

    /**
     * @param array $availabilityStorageEntityCollection
     * @param int $idAvailability
     * @param string $storeName
     *
     * @return bool
     */
    protected function isExistingEntity(array $availabilityStorageEntityCollection, $idAvailability, $storeName)
    {
        return (isset($availabilityStorageEntityCollection[$idAvailability]) && $availabilityStorageEntityCollection[$idAvailability]->getStore() === $storeName);
    }
}
