<?php

/**
 * (c) Spryker Systems GmbH copyright protected
 */

namespace Spryker\Zed\Heartbeat\Communication\Plugin;

use Generated\Shared\Transfer\HealthIndicatorReportTransfer;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Spryker\Shared\Heartbeat\Code\HealthIndicatorInterface;
use Spryker\Zed\Heartbeat\Business\HeartbeatFacade;

/**
 * @method HeartbeatFacade getFacade()
 */
class SearchHealthIndicatorPlugin extends AbstractPlugin implements HealthIndicatorInterface
{

    /**
     * @return HealthIndicatorReportTransfer
     */
    public function doHealthCheck()
    {
        return $this->getFacade()->doSearchHealthCheck();
    }

}