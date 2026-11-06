<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\AvailabilityStorage\Communication\Plugin\Synchronization;

use Codeception\Test\Unit;
use Spryker\Zed\AvailabilityStorage\Communication\Plugin\Synchronization\AvailabilitySynchronizationDataPlugin;
use Spryker\Zed\Synchronization\Communication\Plugin\Synchronization\SynchronizationDataQueryExpanderWhereBetweenStrategyPlugin;
use Spryker\Zed\SynchronizationExtension\Dependency\Plugin\SynchronizationDataQueryContainerPluginInterface;
use SprykerTest\Zed\AvailabilityStorage\AvailabilityStorageCommunicationTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group AvailabilityStorage
 * @group Communication
 * @group Plugin
 * @group Synchronization
 * @group AvailabilityStorageSynchronizationDataExportTest
 * Add your own group annotations below this line
 */
class AvailabilityStorageSynchronizationDataExportTest extends Unit
{
    protected AvailabilityStorageCommunicationTester $tester;

    /**
     * @dataProvider synchronizationDataPluginDataProvider
     */
    public function testExportsAllRowsWhenLowestPrimaryKeyIsAboveChunkSize(SynchronizationDataQueryContainerPluginInterface $synchronizationDataPlugin): void
    {
        // Arrange
        $expectedKeys = $this->tester->haveOnlySynchronizationEntitiesWithPrimaryKeys($synchronizationDataPlugin, [1001, 1002, 1003]);

        // Act
        $sentMessageKeys = $this->tester->exportSynchronizationData($synchronizationDataPlugin, new SynchronizationDataQueryExpanderWhereBetweenStrategyPlugin());

        // Assert
        $this->tester->assertSynchronizationKeysExported($expectedKeys, $sentMessageKeys);
    }

    /**
     * @dataProvider synchronizationDataPluginDataProvider
     */
    public function testExportsRowsLocatedAfterPrimaryKeyGapLargerThanChunkSize(
        SynchronizationDataQueryContainerPluginInterface $synchronizationDataPlugin
    ): void {
        // Arrange
        $expectedKeys = $this->tester->haveOnlySynchronizationEntitiesWithPrimaryKeys($synchronizationDataPlugin, [1, 2, 500, 501]);

        // Act
        $sentMessageKeys = $this->tester->exportSynchronizationData($synchronizationDataPlugin, new SynchronizationDataQueryExpanderWhereBetweenStrategyPlugin());

        // Assert
        $this->tester->assertSynchronizationKeysExported($expectedKeys, $sentMessageKeys);
    }

    /**
     * @dataProvider synchronizationDataPluginDataProvider
     */
    public function testExportsAllRowsWhenLowestPrimaryKeyIsAboveChunkSizeWithKeysetPagination(
        SynchronizationDataQueryContainerPluginInterface $synchronizationDataPlugin
    ): void {
        // Arrange
        $expectedKeys = $this->tester->haveOnlySynchronizationEntitiesWithPrimaryKeys($synchronizationDataPlugin, [1001, 1002, 1003]);

        // Act
        $sentMessageKeys = $this->tester->exportSynchronizationDataWithKeysetPagination($synchronizationDataPlugin);

        // Assert
        $this->tester->assertSynchronizationKeysExported($expectedKeys, $sentMessageKeys);
    }

    /**
     * @dataProvider synchronizationDataPluginDataProvider
     */
    public function testExportsRowsLocatedAfterPrimaryKeyGapLargerThanChunkSizeWithKeysetPagination(
        SynchronizationDataQueryContainerPluginInterface $synchronizationDataPlugin
    ): void {
        // Arrange
        $expectedKeys = $this->tester->haveOnlySynchronizationEntitiesWithPrimaryKeys($synchronizationDataPlugin, [1, 2, 500, 501]);

        // Act
        $sentMessageKeys = $this->tester->exportSynchronizationDataWithKeysetPagination($synchronizationDataPlugin);

        // Assert
        $this->tester->assertSynchronizationKeysExported($expectedKeys, $sentMessageKeys);
    }

    /**
     * @return array<string, array<\Spryker\Zed\SynchronizationExtension\Dependency\Plugin\SynchronizationDataQueryContainerPluginInterface>>
     */
    public static function synchronizationDataPluginDataProvider(): array
    {
        return [
            'availability' => [new AvailabilitySynchronizationDataPlugin()],
        ];
    }
}
