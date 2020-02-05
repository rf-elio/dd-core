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
     * @return bool
     * @throws \Shopware\Core\Framework\DataAbstractionLayer\Exception\InconsistentCriteriaIdsException
     */
    public function install():bool;

    /**
     * generate product export file
     *
     * @return array content the generated filenames and the amount of generated products
     */
    public function generate():array;
}
