<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Acceptance\Sales\Order\Zed\PageObject;

class SalesDetailPage
{

    const URL = '/sales/detail?id-sales-order=';

    const SELECTOR_ID_SALES_ORDER_ITEM = '//div[@id="items"]//table/tbody/tr/td[{{position}}]/input';
    const SELECTOR_SALES_ORDER_ROW = '//div[@id="items"]//table/tbody/tr/td[{{position}}]/input';

    /**
     * @param int $idSalesOrder
     *
     * @return string
     */
    public static function getOrderDetailsPageUrl($idSalesOrder)
    {
        return static::URL . $idSalesOrder;
    }

    /**
     * @param int $rowPosition Position of row in list, starts with 1
     *
     * @return string
     */
    public static function getIdSalesOrderItemSelector($rowPosition)
    {
        return str_replace('{{position}}', $rowPosition, static::SELECTOR_ID_SALES_ORDER_ITEM);
    }

    /**
     * @param int $rowPosition Position of row in list, starts with 1
     *
     * @return string
     */
    public static function getSalesOrderItemRowSelector($rowPosition)
    {
        return str_replace('{{position}}', $rowPosition, static::SELECTOR_SALES_ORDER_ROW);
    }

}