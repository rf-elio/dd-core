<?php declare(strict_types=1);

namespace Elio\FactFinder\Service\Export;

use Shopware\Core\Content\ProductExport\Service\ProductExportGeneratorInterface;

/**
 * interface ExporterInterface
 * @category  Service
 * @package   Shopware\Plugins\FactFinder\Service\Export
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (http://www.elio-systems.com)
 */
interface ExporterInterface extends ProductExportGeneratorInterface
{
    /**
     * CSV-based export format.
     */
    const TYPE_CSV = 1;

    /**
     * Creates an exporter for the desired output format.
     *
     * @param int $type
     * @return Exporter The exporter for the desired output format.
     */
    public function create(int $type): Exporter;

}
