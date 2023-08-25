<?php


namespace Elio\ElioSearch\Tests\Core\Export\Generator;


use Elio\ElioSearch\Core\Export\ExportEntity;
use Elio\ElioSearch\Core\Export\ExportStorageService;
use Elio\ElioSearch\Core\Export\Generator\Product\ProductExportDefaults;
use Elio\ElioSearch\Core\Export\Generator\Product\ProductExportGenerator;
use Elio\ElioSearch\Core\Export\OutputStream;
use Elio\ElioSearch\Core\Export\Writer\CSVFileWriter;
use Elio\ElioSearch\Core\Features\FeatureService;
use Elio\ElioSearch\Tests\Core\Export\Mock\EventDispatcherMock;
use Elio\ElioSearch\Tests\Core\Export\Mock\Repository\SalesChannelRepositoryMock;
use Elio\ElioSearch\Tests\Core\Export\Mock\Repository\ProductRepositoryMock;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Delivery\Struct\ShippingLocation;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupEntity;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Api\Context\SystemSource;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Event\EntityLoadedEventFactory;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use Shopware\Core\Framework\DataAbstractionLayer\Read\EntityReaderInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntityAggregatorInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\EntitySearcherInterface;
use Shopware\Core\Framework\DataAbstractionLayer\VersionManager;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\Currency\CurrencyDefinition;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelDefinition;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\Tax\TaxCollection;

/**
 * Class ProductExportGeneratorTest
 *
 * @package Elio\ElioSearch\Tests\Core\Export\Generator
 */
class ProductExportGeneratorTest extends TestCase
{
    use KernelTestBehaviour;

    private ProductExportGenerator $generator;

    public function setUp(): void
    {
        $this->generator = new ProductExportGenerator(

            new ProductRepositoryMock(
                self::getContainer()->get(ProductDefinition::class),
                self::getContainer()->get(EntityReaderInterface::class),
                self::getContainer()->get(VersionManager::class),
                self::getContainer()->get(EntitySearcherInterface::class),
                self::getContainer()->get(EntityAggregatorInterface::class),
                self::getContainer()->get('event_dispatcher'),
                self::getContainer()->get(EntityLoadedEventFactory::class)
            ),
            new EventDispatcherMock(),
            new SalesChannelRepositoryMock(
                self::getContainer()->get(SalesChannelDefinition::class),
                self::getContainer()->get(EntityReaderInterface::class),
                self::getContainer()->get(VersionManager::class),
                self::getContainer()->get(EntitySearcherInterface::class),
                self::getContainer()->get(EntityAggregatorInterface::class),
                self::getContainer()->get('event_dispatcher'),
                self::getContainer()->get(EntityLoadedEventFactory::class)
            ),
            new FeatureService()
        );
    }

    /**
     * @dataProvider supportsProvider
     */
    public function testSupports($type, $expected): void
    {
        $exportEntity = new ExportEntity();
        $exportEntity->setType($type);
        self::assertSame($expected, $this->generator->supports($exportEntity));
    }

    public function testGetModel(): void
    {
        $exportEntity = new ExportEntity();
        $exportEntity->setMapping([
            [
                'source' => 'foo',
                'target' => 'Foo'
            ],
            [
                'source' => 'bar',
                'target' => 'Bar'
            ]
        ]);

        self::assertSame([
            ProductExportDefaults::FIELD_ID,
            ProductExportDefaults::FIELD_MASTER_PRODUCT_NUMBER,
            ProductExportDefaults::FIELD_PRODUCT_ID,
            ProductExportDefaults::FIELD_MANUFACTURER_NUMBER,
            ProductExportDefaults::FIELD_NAME,
            ProductExportDefaults::FIELD_DESCRIPTION,
            ProductExportDefaults::FIELD_META_TITLE,
            ProductExportDefaults::FIELD_PRODUCT_URL,
            ProductExportDefaults::FIELD_PRICE,
            ProductExportDefaults::FIELD_RED_PRICE,
            ProductExportDefaults::FIELD_MANUFACTURER,
            ProductExportDefaults::FIELD_CATEGORY_PATH,
            ProductExportDefaults::FIELD_CATEGORY_IDS,
            ProductExportDefaults::FIELD_EAN,
            ProductExportDefaults::FIELD_KEYWORDS,
            ProductExportDefaults::FIELD_SEARCH_KEYWORDS,
            ProductExportDefaults::FIELD_STOCK,
            ProductExportDefaults::FIELD_CLOSEOUT,
            ProductExportDefaults::FIELD_RATING_AVERAGE,
            ProductExportDefaults::FIELD_RATING_COUNT,
            ProductExportDefaults::FIELD_SHIPPING_FREE,
            ProductExportDefaults::FIELD_ATTRIBUTE,
            ProductExportDefaults::FIELD_ATTRIBUTE_NON_FILTERABLE,
            ProductExportDefaults::FIELD_IMAGE_URL,
            ProductExportDefaults::FIELD_THUMBNAIL_URL,
            ProductExportDefaults::FIELD_TAGS,
            ProductExportDefaults::FIELD_RELEASE_DATE,
            ProductExportDefaults::FIELD_SALES_COUNT,
            'Foo',
            'Bar'
        ], $this->generator->getModel($exportEntity));
    }

    public function testGenerate(): void
    {
        $container = self::getContainer();
        /** @var FilesystemOperator $fileSystem */
        $fileSystem = $container->get('shopware.filesystem.private');
        $exportStorageService = new ExportStorageService($fileSystem);
        $writer = new CSVFileWriter($exportStorageService);

        $exportEntity = new ExportEntity();
        $exportEntity->setId(Uuid::randomHex());
        $exportEntity->setSalesChannelId(Uuid::randomHex());
        $exportEntity->setLanguageId(Uuid::randomHex());
        $exportEntity->setName('test');
        $exportEntity->setFormat(CSVFileWriter::TYPE);
        $exportEntity->setMapping([]);

        $context = $this->getSalesChannelContext();

        $stream = new OutputStream($writer, $exportEntity, $context);
        $stream->open($context);
        $stream->registerModel($this->generator->getModel($exportEntity));

        $this->generator->generate($exportEntity, $stream, $context);

        $stream->close();

        $content = $fileSystem->read($exportStorageService->createFileName($exportEntity));
        $fileSystem->delete($exportStorageService->createFileName($exportEntity));

        $rows = str_getcsv($content, "\n");

        // fields
        self::assertSame(
            'ID;MasterProductNumber;ProductID;ManufacturerNumber;Name;Description;MetaTitle;ProductURL;Price;RedPrice;Manufacturer;CategoryPath;CategoryIds;EAN;Keywords;SearchKeywords;Stock;Closeout;RatingAverage;RatingCount;ShippingFree;Attribute;AttributeNonFilterable;ImageURL;ThumbnailURL;Tags;ReleaseDate;SalesCount',
            $rows[0]
        );
        // first product
        self::assertSame(
            Uuid::fromStringToHex('product1') . ';productNumber1;productNumber1;test;product1;test;;;200.00;;test;breadcrumb%203/breadcrumb%204;1/2/3;;;;1;0;;0;;|;|;;;;;1',
            $rows[1]
        );
        // second product
        self::assertSame(
            Uuid::fromStringToHex('product2') . ';productNumber2;productNumber2;test;product2;test;;;200.00;;test;breadcrumb%203/breadcrumb%204;1/2/3;;;;1;0;;0;;|;|;;;;;1',
            $rows[2]
        );
    }

    public function supportsProvider(): array
    {
        return [
            [
                'test',
                false
            ],
            [
                'test2',
                false
            ],
            [
                ProductExportDefaults::TYPE,
                true
            ]
        ];
    }

    /**
     * @return SalesChannelContext
     */
    private function getSalesChannelContext(): SalesChannelContext
    {
        $salesChannel = new SalesChannelEntity();
        $salesChannel->setId(Defaults::SALES_CHANNEL_TYPE_STOREFRONT);

        return new SalesChannelContext(
            new Context(new SystemSource()),
            '',
            null,
            $salesChannel,
            new CurrencyEntity(),
            new CustomerGroupEntity(),
            new TaxCollection(),
            new PaymentMethodEntity(),
            new ShippingMethodEntity(),
            new ShippingLocation(new CountryEntity(), null, null),
            null,
            new CashRoundingConfig(0, 0, false),
            new CashRoundingConfig(0, 0, false)
        );
    }
}
