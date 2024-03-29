<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AvailabilityStorage\Persistence;

use Orm\Zed\Availability\Persistence\Map\SpyAvailabilityAbstractTableMap;
use Orm\Zed\Availability\Persistence\SpyAvailabilityAbstractQuery;
use Orm\Zed\Product\Persistence\Map\SpyProductAbstractTableMap;
use Orm\Zed\Product\Persistence\SpyProductAbstractQuery;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Spryker\Zed\Kernel\Persistence\AbstractQueryContainer;
use Spryker\Zed\PropelOrm\Business\Runtime\ActiveQuery\Criteria;

/**
 * @method \Spryker\Zed\AvailabilityStorage\Persistence\AvailabilityStoragePersistenceFactory getFactory()
 */
class AvailabilityStorageQueryContainer extends AbstractQueryContainer implements AvailabilityStorageQueryContainerInterface
{
    /**
     * @var string
     */
    public const ID_PRODUCT_ABSTRACT = 'id_product_abstract';

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param array $abstractProductIds
     *
     * @return \Orm\Zed\AvailabilityStorage\Persistence\SpyAvailabilityStorageQuery
     */
    public function queryAvailabilityStorageByProductAbstractIds(array $abstractProductIds)
    {
        return $this->getFactory()
            ->createSpyAvailabilityStorageQuery()
            ->filterByFkProductAbstract_In($abstractProductIds);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param array $availabilityAbstractIds
     *
     * @return \Orm\Zed\AvailabilityStorage\Persistence\SpyAvailabilityStorageQuery
     */
    public function queryAvailabilityStorageByAvailabilityAbstractIds(array $availabilityAbstractIds)
    {
        return $this->getFactory()
            ->createSpyAvailabilityStorageQuery()
            ->filterByFkAvailabilityAbstract_In($availabilityAbstractIds);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param array $availabilityAbstractIds
     *
     * @return \Orm\Zed\Availability\Persistence\SpyAvailabilityAbstractQuery
     */
    public function queryAvailabilityByAvailabilityAbstractIds(array $availabilityAbstractIds): SpyAvailabilityAbstractQuery
    {
        return $this->getFactory()
            ->getAvailabilityQueryContainer()
            ->queryAllAvailabilityAbstracts()
            ->filterByIdAvailabilityAbstract_In($availabilityAbstractIds);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param array $availabilityAbstractIds
     *
     * @return \Orm\Zed\Availability\Persistence\SpyAvailabilityAbstractQuery
     */
    public function queryAvailabilityAbstractWithRelationsByIds(array $availabilityAbstractIds)
    {
        return $this->getFactory()
            ->getAvailabilityQueryContainer()
            ->queryAllAvailabilityAbstracts()
            ->joinWithSpyAvailability()
            ->joinWithStore()
            ->addJoin(
                SpyAvailabilityAbstractTableMap::COL_ABSTRACT_SKU,
                SpyProductAbstractTableMap::COL_SKU,
                Criteria::INNER_JOIN,
            )
            ->filterByIdAvailabilityAbstract_In($availabilityAbstractIds)
            ->withColumn(SpyProductAbstractTableMap::COL_ID_PRODUCT_ABSTRACT, static::ID_PRODUCT_ABSTRACT)
            ->setFormatter(ModelCriteria::FORMAT_ARRAY);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param array $abstractProductSkus
     *
     * @return \Orm\Zed\Availability\Persistence\SpyAvailabilityAbstractQuery
     */
    public function queryAvailabilityAbstractByAbstractProductSkus(array $abstractProductSkus)
    {
        return $this->getFactory()
            ->getAvailabilityQueryContainer()
            ->queryAllAvailabilityAbstracts()
            ->filterByAbstractSku_In($abstractProductSkus)
            ->select([SpyAvailabilityAbstractTableMap::COL_ID_AVAILABILITY_ABSTRACT]);
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param array $abstractProductIds
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductAbstractQuery
     */
    public function queryProductAbstractWithProductByAbstractProductIds(array $abstractProductIds)
    {
        return $this->getFactory()
            ->getProductQueryContainer()
            ->queryProductAbstract()
            ->filterByIdProductAbstract_In($abstractProductIds)
            ->joinWithSpyProduct();
    }

    /**
     * {@inheritDoc}
     *
     * @api
     *
     * @param array<int> $abstractProductIds
     *
     * @return \Orm\Zed\Product\Persistence\SpyProductAbstractQuery
     */
    public function queryProductAbstractByIds(array $abstractProductIds): SpyProductAbstractQuery
    {
        return $this->getFactory()
            ->getProductQueryContainer()
            ->queryProductAbstract()
            ->filterByIdProductAbstract_In($abstractProductIds);
    }
}
