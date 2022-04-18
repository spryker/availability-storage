<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\AvailabilityStorage\Communication\Plugin\Event\Listener;

use Spryker\Zed\Availability\Dependency\AvailabilityEvents;
use Spryker\Zed\Event\Dependency\Plugin\EventBulkHandlerInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;

/**
 * @deprecated Use {@link \Spryker\Zed\AvailabilityStorage\Communication\Plugin\Event\Listener\AvailabilityStoragePublishListener}
 *   and {@link \Spryker\Zed\AvailabilityStorage\Communication\Plugin\Event\Listener\AvailabilityStorageUnpublishListener} instead.
 *
 * @method \Spryker\Zed\AvailabilityStorage\Persistence\AvailabilityStorageQueryContainerInterface getQueryContainer()
 * @method \Spryker\Zed\AvailabilityStorage\Communication\AvailabilityStorageCommunicationFactory getFactory()
 * @method \Spryker\Zed\AvailabilityStorage\Business\AvailabilityStorageFacadeInterface getFacade()
 * @method \Spryker\Zed\AvailabilityStorage\AvailabilityStorageConfig getConfig()
 */
class AvailabilityStorageListener extends AbstractPlugin implements EventBulkHandlerInterface
{
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
        $availabilityIds = $this->getFactory()->getEventBehaviorFacade()->getEventTransferIds($eventEntityTransfers);

        if (
            $eventName === AvailabilityEvents::ENTITY_SPY_AVAILABILITY_ABSTRACT_DELETE ||
            $eventName === AvailabilityEvents::AVAILABILITY_ABSTRACT_UNPUBLISH
        ) {
            $this->getFacade()->unpublish($availabilityIds);

            return;
        }

        $this->getFacade()->publish($availabilityIds);
    }
}
