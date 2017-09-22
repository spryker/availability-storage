<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Currency\Dependency\Facade;

class CurrencyToStoreBridge implements CurrencyToStoreInterface
{

    /**
     * @var \Spryker\Zed\Store\Business\StoreFacadeInterface
     */
    protected $storeFacade;

    /**
     * @param \Spryker\Zed\Store\Business\StoreFacadeInterface
     */
    public function __construct($storeFacade)
    {
        $this->storeFacade = $storeFacade;
    }

    /**
     * @return \Generated\Shared\Transfer\StoreTransfer[]
     */
    public function getAllActiveStores()
    {
        return $this->storeFacade->getAllActiveStores();
    }

    /**
     * @return string
     */
    public function getCurrencyIsoCode()
    {
       return $this->storeFacade->getCurrencyIsoCode();
    }

    /**
     * @return array
     */
    public function getCurrencyIsoCodes()
    {
        return $this->storeFacade->getCurrencyIsoCodes();
    }

    /**
     * @param string $storeName
     *
     * @return \Generated\Shared\Transfer\StoreTransfer[]
     */
    public function getAvailableCurrenciesForStore($storeName)
    {
        return $this->storeFacade->getAvailableCurrenciesForStore($storeName);
    }

    /**
     * @return \Generated\Shared\Transfer\StoreTransfer
     */
    public function getCurrentStore()
    {
        return $this->storeFacade->getCurrentStore();
    }
}
