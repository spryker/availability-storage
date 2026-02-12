<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Client\AvailabilityStorage\Plugin;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\ProductAbstractAvailabilityTransfer;
use Generated\Shared\Transfer\ProductConcreteAvailabilityTransfer;
use Generated\Shared\Transfer\ProductViewTransfer;
use Generated\Shared\Transfer\StoreTransfer;
use PHPUnit\Framework\MockObject\MockObject;
use Spryker\Client\AvailabilityStorage\AvailabilityStorageDependencyProvider;
use Spryker\Client\AvailabilityStorage\Dependency\Client\AvailabilityStorageToStorageClientInterface;
use Spryker\Client\AvailabilityStorage\Dependency\Client\AvailabilityStorageToStoreClientInterface;
use Spryker\Client\AvailabilityStorage\Plugin\ProductViewAvailabilityStorageExpanderPlugin;
use Spryker\Client\AvailabilityStorageExtension\Dependency\Plugin\AvailabilityStorageStrategyPluginInterface;
use Spryker\DecimalObject\Decimal;
use SprykerTest\Client\AvailabilityStorage\AvailabilityStorageClientTester;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Client
 * @group AvailabilityStorage
 * @group Plugin
 * @group ProductViewAvailabilityStorageExpanderPluginTest
 * Add your own group annotations below this line
 */
class ProductViewAvailabilityStorageExpanderPluginTest extends Unit
{
    protected AvailabilityStorageClientTester $tester;

    protected AvailabilityStorageToStorageClientInterface|MockObject $storageClientMock;

    protected AvailabilityStorageToStoreClientInterface|MockObject $storeClientMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->storageClientMock = $this->createMock(AvailabilityStorageToStorageClientInterface::class);
        $storeTransfer = (new StoreTransfer())->setName('DE');
        $this->storeClientMock = $this->createMock(AvailabilityStorageToStoreClientInterface::class);
        $this->storeClientMock->method('getCurrentStore')->willReturn($storeTransfer);

        $this->tester->setDependency(AvailabilityStorageDependencyProvider::CLIENT_STORAGE, $this->storageClientMock);
        $this->tester->setDependency(AvailabilityStorageDependencyProvider::CLIENT_STORE, $this->storeClientMock);
    }

    protected function setUpDependencies(?array $storageData, ?array $strategyPlugins = []): void
    {
        $this->storageClientMock->method('get')->willReturnCallback(fn () => $storageData);

        if ($strategyPlugins) {
            $this->tester->setDependency(AvailabilityStorageDependencyProvider::PLUGINS_AVAILABILITY_STORAGE_STRATEGY, $strategyPlugins);
        }
    }

    /**
     * @dataProvider expandProductViewTransferWithAbstractProductDataProvider
     */
    public function testExpandProductViewTransferWithAbstractProduct(
        ProductViewTransfer $productViewTransfer,
        ?ProductAbstractAvailabilityTransfer $productAbstractAvailabilityTransfer,
        bool $expectedAvailable
    ): void {
        // Arrange
        $storageData = $productAbstractAvailabilityTransfer
            ? $this->convertTransferToStorageData($productAbstractAvailabilityTransfer)
            : null;
        $this->setUpDependencies($storageData);
        $plugin = new ProductViewAvailabilityStorageExpanderPlugin();

        // Act
        $result = $plugin->expandProductViewTransfer($productViewTransfer, [], 'en_US');

        // Assert
        $this->assertInstanceOf(ProductViewTransfer::class, $result);
        $this->assertSame($expectedAvailable, $result->getAvailable());
    }

    /**
     * @dataProvider expandProductViewTransferWithConcreteProductDataProvider
     */
    public function testExpandProductViewTransferWithConcreteProduct(
        ProductViewTransfer $productViewTransfer,
        ProductAbstractAvailabilityTransfer $productAbstractAvailabilityTransfer,
        bool $expectedAvailable,
        ?float $expectedStockQuantity,
        bool $expectedIsNeverOutOfStock
    ): void {
        // Arrange
        $storageData = $this->convertTransferToStorageData($productAbstractAvailabilityTransfer);
        $this->setUpDependencies($storageData);
        $plugin = new ProductViewAvailabilityStorageExpanderPlugin();

        // Act
        $result = $plugin->expandProductViewTransfer($productViewTransfer, [], 'en_US');

        // Assert
        $this->assertInstanceOf(ProductViewTransfer::class, $result);
        $this->assertSame($expectedAvailable, $result->getAvailable());
        $this->assertSame($expectedStockQuantity, $result->getStockQuantity());
        $this->assertSame($expectedIsNeverOutOfStock, $result->getIsNeverOutOfStock());
    }

    /**
     * @dataProvider expandProductViewTransferWithStrategyPluginDataProvider
     */
    public function testExpandProductViewTransferWithStrategyPlugin(
        ProductViewTransfer $productViewTransfer,
        ProductAbstractAvailabilityTransfer $productAbstractAvailabilityTransfer,
        bool $strategyPluginIsApplicable,
        bool $strategyPluginResult,
        bool $expectedAvailable
    ): void {
        // Arrange
        $strategyPlugin = $this->createMock(AvailabilityStorageStrategyPluginInterface::class);
        $strategyPlugin->method('isApplicable')->willReturn($strategyPluginIsApplicable);
        $strategyPlugin->method('isProductAvailable')->willReturn($strategyPluginResult);

        $storageData = $this->convertTransferToStorageData($productAbstractAvailabilityTransfer);
        $this->setUpDependencies($storageData, [$strategyPlugin]);

        $plugin = new ProductViewAvailabilityStorageExpanderPlugin();

        // Act
        $result = $plugin->expandProductViewTransfer($productViewTransfer, [], 'en_US');

        // Assert
        $this->assertSame($expectedAvailable, $result->getAvailable());
    }

    public function testExpandProductViewTransferWhenStorageReaderReturnsNull(): void
    {
        // Arrange
        $productViewTransfer = (new ProductViewTransfer())
            ->setIdProductAbstract(999);

        $this->setUpDependencies(null);

        $plugin = new ProductViewAvailabilityStorageExpanderPlugin();

        // Act
        $result = $plugin->expandProductViewTransfer($productViewTransfer, [], 'en_US');

        // Assert
        $this->assertFalse($result->getAvailable());
        $this->assertNull($result->getStockQuantity());
        $this->assertNull($result->getIsNeverOutOfStock());
    }

    public function testExpandProductViewTransferWithMissingIdProductAbstract(): void
    {
        // Arrange
        $productViewTransfer = new ProductViewTransfer();
        $this->setUpDependencies(null);
        $plugin = new ProductViewAvailabilityStorageExpanderPlugin();

        // Act
        $result = $plugin->expandProductViewTransfer($productViewTransfer, [], 'en_US');

        // Assert
        $this->assertFalse($result->getAvailable());
        $this->assertNull($result->getStockQuantity());
        $this->assertNull($result->getIsNeverOutOfStock());
    }

    public function testExpandProductViewTransferWithConcreteProductNotFoundInAvailabilityData(): void
    {
        // Arrange
        $productViewTransfer = (new ProductViewTransfer())
            ->setIdProductAbstract(1)
            ->setIdProductConcrete(999)
            ->setSku('non-existent-sku');

        $productAbstractAvailabilityTransfer = (new ProductAbstractAvailabilityTransfer())
            ->setAvailability(new Decimal(10))
            ->setIsNeverOutOfStock(false)
            ->addProductConcreteAvailability(
                (new ProductConcreteAvailabilityTransfer())
                    ->setSku('different-sku')
                    ->setAvailability(new Decimal(5))
                    ->setIsNeverOutOfStock(false),
            );

        $storageData = $this->convertTransferToStorageData($productAbstractAvailabilityTransfer);
        $this->setUpDependencies($storageData);
        $plugin = new ProductViewAvailabilityStorageExpanderPlugin();

        // Act
        $result = $plugin->expandProductViewTransfer($productViewTransfer, [], 'en_US');

        // Assert
        $this->assertInstanceOf(ProductViewTransfer::class, $result);
    }

    /**
     * @return array<string, array{productViewTransfer: \Generated\Shared\Transfer\ProductViewTransfer, productAbstractAvailabilityTransfer: \Generated\Shared\Transfer\ProductAbstractAvailabilityTransfer|null, expectedAvailable: bool, expectedStockQuantity: float, expectedIsNeverOutOfStock: bool}>
     */
    public function expandProductViewTransferWithAbstractProductDataProvider(): array
    {
        return [
            'abstract product with positive availability' => [
                'productViewTransfer' => (new ProductViewTransfer())->setIdProductAbstract(1),
                'productAbstractAvailabilityTransfer' => (new ProductAbstractAvailabilityTransfer())
                    ->setAvailability(new Decimal(10)),
                'expectedAvailable' => true,
            ],
            'abstract product with zero availability' => [
                'productViewTransfer' => (new ProductViewTransfer())->setIdProductAbstract(2),
                'productAbstractAvailabilityTransfer' => (new ProductAbstractAvailabilityTransfer())
                    ->setAvailability(new Decimal(0)),
                'expectedAvailable' => false,
            ],
            'abstract product with negative availability' => [
                'productViewTransfer' => (new ProductViewTransfer())->setIdProductAbstract(3),
                'productAbstractAvailabilityTransfer' => (new ProductAbstractAvailabilityTransfer())
                    ->setAvailability(new Decimal(-5)),
                'expectedAvailable' => false,
            ],
            'abstract product never out of stock with zero availability' => [
                'productViewTransfer' => (new ProductViewTransfer())->setIdProductAbstract(4),
                'productAbstractAvailabilityTransfer' => (new ProductAbstractAvailabilityTransfer())
                    ->setAvailability(new Decimal(0)),
                'expectedAvailable' => false,
            ],
            'abstract product never out of stock with positive availability' => [
                'productViewTransfer' => (new ProductViewTransfer())->setIdProductAbstract(5),
                'productAbstractAvailabilityTransfer' => (new ProductAbstractAvailabilityTransfer())
                    ->setAvailability(new Decimal(100)),
                'expectedAvailable' => true,
            ],
            'abstract product with decimal availability' => [
                'productViewTransfer' => (new ProductViewTransfer())->setIdProductAbstract(6),
                'productAbstractAvailabilityTransfer' => (new ProductAbstractAvailabilityTransfer())
                    ->setAvailability(new Decimal(15.75))
                    ->setIsNeverOutOfStock(false),
                'expectedAvailable' => true,
            ],
        ];
    }

    /**
     * @return array<string, array{productViewTransfer: \Generated\Shared\Transfer\ProductViewTransfer, productAbstractAvailabilityTransfer: \Generated\Shared\Transfer\ProductAbstractAvailabilityTransfer, expectedAvailable: bool, expectedStockQuantity: float|null, expectedIsNeverOutOfStock: bool}>
     */
    public function expandProductViewTransferWithConcreteProductDataProvider(): array
    {
        return [
            'concrete product with positive availability' => [
                'productViewTransfer' => (new ProductViewTransfer())
                    ->setIdProductAbstract(1)
                    ->setIdProductConcrete(101)
                    ->setSku('concrete-sku-1'),
                'productAbstractAvailabilityTransfer' => (new ProductAbstractAvailabilityTransfer())
                    ->setAvailability(new Decimal(100))
                    ->setIsNeverOutOfStock(false)
                    ->addProductConcreteAvailability(
                        (new ProductConcreteAvailabilityTransfer())
                            ->setSku('concrete-sku-1')
                            ->setAvailability(new Decimal(20))
                            ->setIsNeverOutOfStock(false),
                    ),
                'expectedAvailable' => true,
                'expectedStockQuantity' => 20.0,
                'expectedIsNeverOutOfStock' => false,
            ],
            'concrete product with zero availability' => [
                'productViewTransfer' => (new ProductViewTransfer())
                    ->setIdProductAbstract(2)
                    ->setIdProductConcrete(102)
                    ->setSku('concrete-sku-2'),
                'productAbstractAvailabilityTransfer' => (new ProductAbstractAvailabilityTransfer())
                    ->setAvailability(new Decimal(100))
                    ->setIsNeverOutOfStock(false)
                    ->addProductConcreteAvailability(
                        (new ProductConcreteAvailabilityTransfer())
                            ->setSku('concrete-sku-2')
                            ->setAvailability(new Decimal(0))
                            ->setIsNeverOutOfStock(false),
                    ),
                'expectedAvailable' => false,
                'expectedStockQuantity' => 0.0,
                'expectedIsNeverOutOfStock' => false,
            ],
            'concrete product never out of stock with zero availability' => [
                'productViewTransfer' => (new ProductViewTransfer())
                    ->setIdProductAbstract(3)
                    ->setIdProductConcrete(103)
                    ->setSku('concrete-sku-3'),
                'productAbstractAvailabilityTransfer' => (new ProductAbstractAvailabilityTransfer())
                    ->setAvailability(new Decimal(100))
                    ->setIsNeverOutOfStock(false)
                    ->addProductConcreteAvailability(
                        (new ProductConcreteAvailabilityTransfer())
                            ->setSku('concrete-sku-3')
                            ->setAvailability(new Decimal(0))
                            ->setIsNeverOutOfStock(true),
                    ),
                'expectedAvailable' => true,
                'expectedStockQuantity' => 0.0,
                'expectedIsNeverOutOfStock' => true,
            ],
            'concrete product with null availability' => [
                'productViewTransfer' => (new ProductViewTransfer())
                    ->setIdProductAbstract(4)
                    ->setIdProductConcrete(104)
                    ->setSku('concrete-sku-4'),
                'productAbstractAvailabilityTransfer' => (new ProductAbstractAvailabilityTransfer())
                    ->setAvailability(new Decimal(100))
                    ->setIsNeverOutOfStock(false)
                    ->addProductConcreteAvailability(
                        (new ProductConcreteAvailabilityTransfer())
                            ->setSku('concrete-sku-4')
                            ->setAvailability(null)
                            ->setIsNeverOutOfStock(false),
                    ),
                'expectedAvailable' => false,
                'expectedStockQuantity' => null,
                'expectedIsNeverOutOfStock' => false,
            ],
            'concrete product never out of stock with null availability' => [
                'productViewTransfer' => (new ProductViewTransfer())
                    ->setIdProductAbstract(5)
                    ->setIdProductConcrete(105)
                    ->setSku('concrete-sku-5'),
                'productAbstractAvailabilityTransfer' => (new ProductAbstractAvailabilityTransfer())
                    ->setAvailability(new Decimal(100))
                    ->setIsNeverOutOfStock(false)
                    ->addProductConcreteAvailability(
                        (new ProductConcreteAvailabilityTransfer())
                            ->setSku('concrete-sku-5')
                            ->setAvailability(null)
                            ->setIsNeverOutOfStock(true),
                    ),
                'expectedAvailable' => true,
                'expectedStockQuantity' => null,
                'expectedIsNeverOutOfStock' => true,
            ],
            'concrete product with negative availability' => [
                'productViewTransfer' => (new ProductViewTransfer())
                    ->setIdProductAbstract(6)
                    ->setIdProductConcrete(106)
                    ->setSku('concrete-sku-6'),
                'productAbstractAvailabilityTransfer' => (new ProductAbstractAvailabilityTransfer())
                    ->setAvailability(new Decimal(100))
                    ->setIsNeverOutOfStock(false)
                    ->addProductConcreteAvailability(
                        (new ProductConcreteAvailabilityTransfer())
                            ->setSku('concrete-sku-6')
                            ->setAvailability(new Decimal(-10))
                            ->setIsNeverOutOfStock(false),
                    ),
                'expectedAvailable' => false,
                'expectedStockQuantity' => -10.0,
                'expectedIsNeverOutOfStock' => false,
            ],
            'concrete product with decimal availability' => [
                'productViewTransfer' => (new ProductViewTransfer())
                    ->setIdProductAbstract(7)
                    ->setIdProductConcrete(107)
                    ->setSku('concrete-sku-7'),
                'productAbstractAvailabilityTransfer' => (new ProductAbstractAvailabilityTransfer())
                    ->setAvailability(new Decimal(100))
                    ->setIsNeverOutOfStock(false)
                    ->addProductConcreteAvailability(
                        (new ProductConcreteAvailabilityTransfer())
                            ->setSku('concrete-sku-7')
                            ->setAvailability(new Decimal(7.5))
                            ->setIsNeverOutOfStock(false),
                    ),
                'expectedAvailable' => true,
                'expectedStockQuantity' => 7.5,
                'expectedIsNeverOutOfStock' => false,
            ],
        ];
    }

    /**
     * @return array<string, array{productViewTransfer: \Generated\Shared\Transfer\ProductViewTransfer, productAbstractAvailabilityTransfer: \Generated\Shared\Transfer\ProductAbstractAvailabilityTransfer, strategyPluginIsApplicable: bool, strategyPluginResult: bool, expectedAvailable: bool}>
     */
    public function expandProductViewTransferWithStrategyPluginDataProvider(): array
    {
        return [
            'strategy plugin applicable and returns true' => [
                'productViewTransfer' => (new ProductViewTransfer())
                    ->setIdProductAbstract(1)
                    ->setIdProductConcrete(101)
                    ->setSku('concrete-sku-1'),
                'productAbstractAvailabilityTransfer' => (new ProductAbstractAvailabilityTransfer())
                    ->setAvailability(new Decimal(0))
                    ->setIsNeverOutOfStock(false)
                    ->addProductConcreteAvailability(
                        (new ProductConcreteAvailabilityTransfer())
                            ->setSku('concrete-sku-1')
                            ->setAvailability(new Decimal(0))
                            ->setIsNeverOutOfStock(false),
                    ),
                'strategyPluginIsApplicable' => true,
                'strategyPluginResult' => true,
                'expectedAvailable' => true,
            ],
            'strategy plugin applicable and returns false' => [
                'productViewTransfer' => (new ProductViewTransfer())
                    ->setIdProductAbstract(2)
                    ->setIdProductConcrete(102)
                    ->setSku('concrete-sku-2'),
                'productAbstractAvailabilityTransfer' => (new ProductAbstractAvailabilityTransfer())
                    ->setAvailability(new Decimal(10))
                    ->setIsNeverOutOfStock(false)
                    ->addProductConcreteAvailability(
                        (new ProductConcreteAvailabilityTransfer())
                            ->setSku('concrete-sku-2')
                            ->setAvailability(new Decimal(10))
                            ->setIsNeverOutOfStock(false),
                    ),
                'strategyPluginIsApplicable' => true,
                'strategyPluginResult' => false,
                'expectedAvailable' => false,
            ],
            'strategy plugin not applicable' => [
                'productViewTransfer' => (new ProductViewTransfer())
                    ->setIdProductAbstract(3)
                    ->setIdProductConcrete(103)
                    ->setSku('concrete-sku-3'),
                'productAbstractAvailabilityTransfer' => (new ProductAbstractAvailabilityTransfer())
                    ->setAvailability(new Decimal(5))
                    ->setIsNeverOutOfStock(false)
                    ->addProductConcreteAvailability(
                        (new ProductConcreteAvailabilityTransfer())
                            ->setSku('concrete-sku-3')
                            ->setAvailability(new Decimal(5))
                            ->setIsNeverOutOfStock(false),
                    ),
                'strategyPluginIsApplicable' => false,
                'strategyPluginResult' => false,
                'expectedAvailable' => true,
            ],
        ];
    }

    /**
     * @param \Generated\Shared\Transfer\ProductAbstractAvailabilityTransfer $productAbstractAvailabilityTransfer
     *
     * @return array<string, mixed>
     */
    protected function convertTransferToStorageData(
        ProductAbstractAvailabilityTransfer $productAbstractAvailabilityTransfer
    ): array {
        $storageData = [
            'abstract_sku' => $productAbstractAvailabilityTransfer->getSku() ?? 'abstract-sku',
            'quantity' => $productAbstractAvailabilityTransfer->getAvailability(),
            'SpyAvailabilities' => [],
        ];

        foreach ($productAbstractAvailabilityTransfer->getProductConcreteAvailabilities() as $concreteAvailability) {
            $storageData['SpyAvailabilities'][] = [
                'sku' => $concreteAvailability->getSku(),
                'quantity' => $concreteAvailability->getAvailability(),
                'is_never_out_of_stock' => $concreteAvailability->getIsNeverOutOfStock() ?? false,
            ];
        }

        return $storageData;
    }
}
