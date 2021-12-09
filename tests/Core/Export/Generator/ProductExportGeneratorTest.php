<?php


namespace Elio\FactFinder\Tests\Core\Export\Generator;


use Elio\FactFinder\Core\Export\ExportEntity;
use Elio\FactFinder\Core\Export\ExportStorageService;
use Elio\FactFinder\Core\Export\Generator\Product\ProductExportDefaults;
use Elio\FactFinder\Core\Export\Generator\Product\ProductExportGenerator;
use Elio\FactFinder\Core\Export\OutputStream;
use Elio\FactFinder\Core\Export\Writer\CSVFileWriter;
use Elio\FactFinder\Tests\Core\Export\Mock\EventDispatcherMock;
use Elio\FactFinder\Tests\Core\Export\Mock\Repository\CurrencyRepositoryMock;
use Elio\FactFinder\Tests\Core\Export\Mock\Repository\ProductRepositoryMock;
use League\Flysystem\FilesystemInterface;
use PHPUnit\Framework\TestCase;
use Shopware\Core\Checkout\Cart\Delivery\Struct\ShippingLocation;
use Shopware\Core\Checkout\Customer\Aggregate\CustomerGroup\CustomerGroupEntity;
use Shopware\Core\Checkout\Payment\PaymentMethodEntity;
use Shopware\Core\Checkout\Shipping\ShippingMethodEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Pricing\CashRoundingConfig;
use Shopware\Core\Framework\Test\TestCaseBase\KernelTestBehaviour;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\System\Country\CountryEntity;
use Shopware\Core\System\Currency\CurrencyEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\System\SalesChannel\SalesChannelEntity;
use Shopware\Core\System\Tax\TaxCollection;

/**
 * Class ProductExportGeneratorTest
 *
 * @package Elio\FactFinder\Tests\Core\Export\Generator
 */
class ProductExportGeneratorTest extends TestCase
{
    use KernelTestBehaviour;

    private ProductExportGenerator $generator;

    public function setUp(): void
    {
        $this->generator = new ProductExportGenerator(
            new ProductRepositoryMock(),
            new CurrencyRepositoryMock(),
            new EventDispatcherMock()
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
            ProductExportDefaults::FIELD_MASTER_PRODUCT_NUMBER,
            ProductExportDefaults::FIELD_PRODUCT_ID,
            ProductExportDefaults::FIELD_MANUFACTURER_NUMBER,
            ProductExportDefaults::FIELD_NAME,
            ProductExportDefaults::FIELD_DESCRIPTION,
            ProductExportDefaults::FIELD_PRODUCT_URL,
            ProductExportDefaults::FIELD_PRICE,
            ProductExportDefaults::FIELD_MANUFACTURER,
            ProductExportDefaults::FIELD_CATEGORY_PATH,
            ProductExportDefaults::FIELD_EAN,
            ProductExportDefaults::FIELD_KEYWORDS,
            ProductExportDefaults::FIELD_SEARCH_KEYWORDS,
            ProductExportDefaults::FIELD_STOCK,
            ProductExportDefaults::FIELD_RATING_AVERAGE,
            ProductExportDefaults::FIELD_SHIPPING_FREE,
            ProductExportDefaults::FIELD_ATTRIBUTE,
            ProductExportDefaults::FIELD_IMAGE_URL,
            ProductExportDefaults::FIELD_TAGS,
            'Foo',
            'Bar'
        ], $this->generator->getModel($exportEntity));
    }

    public function testGenerate(): void
    {
        $container = $this->getContainer();
        /** @var FilesystemInterface $fileSystem */
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
            'MasterProductNumber;ProductID;ManufacturerNumber;Name;Description;ProductURL;Price;Manufacturer;CategoryPath;EAN;Keywords;SearchKeywords;Stock;RatingAverage;ShippingFree;Attribute;ImageURL;Tags',
            $rows[0]
        );
        // first product
        self::assertSame(
            'productNumber1;productNumber1;test;product1;test;;|USD~~$=200|;test;"breadcrumb 3/breadcrumb 4";;;;1;;;;;',
            $rows[1]
        );
        // second product
        self::assertSame(
            'productNumber2;productNumber2;test;product2;test;;|USD~~$=200|;test;"breadcrumb 3/breadcrumb 4";;;;1;;;;;',
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

    private function getSalesChannelContext(): SalesChannelContext
    {
        return new SalesChannelContext(
            Context::createDefaultContext(),
            '',
            null,
            new SalesChannelEntity(),
            new CurrencyEntity(),
            new CustomerGroupEntity(),
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
