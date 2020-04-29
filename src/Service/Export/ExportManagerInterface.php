<?php declare(strict_types=1);

namespace Elio\FactFinder\Service\Export;


/**
 * interface ExportManagerInterface
 * @category  Service
 * @package   Shopware\Plugins\FactFinder\Service\Export
 * @author    Raoul Yemetio <ry@elio-systems.com>
 * @copyright Copyright (c) 2020, elio GmbH (http://www.elio-systems.com)
 */
interface ExportManagerInterface
{
    /**
     * Create product export for all sales channels
     *
     * @return array contents Ids from created or updated product export and when occurred the errors
     */
    public function install(): array;

    /**
     * generate product export file
     *
     * @return array contents the generated filenames, the exported contents, the amount of generated products
     * and when occurred the errors
     */
    public function generate(): array;
}
