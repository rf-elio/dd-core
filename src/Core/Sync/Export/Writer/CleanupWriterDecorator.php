<?php

namespace Elio\ElioSearch\Core\Sync\Export\Writer;


use Elio\ElioSearch\Core\Export\ExportItem;
use Elio\ElioSearch\Core\Sync\SyncProfileEntity;
use Shopware\Core\System\SalesChannel\SalesChannelContext;

/**
 * Class CleanupWriterDecorator
 * @package Elio\ElioSearch\Core\Sync\Export\Writer
 * @category Shopware
 * @author elio GmbH <support@elio-systems.com>
 * @author Danil Lukov <dl@elio-systems.com>
 * @copyright Copyright (c) 2023, elio GmbH (https://www.elio-systems.com)
 */
class CleanupWriterDecorator implements FileWriterInterface
{
    private FileWriterInterface $decorated;

    /**
     * @param FileWriterInterface $decorated
     */
    public function __construct(FileWriterInterface $decorated)
    {
        $this->decorated = $decorated;
    }

    /**
     * All exports are supported for seo injection
     *
     * @param string $format
     * @return bool
     */
    public function supports(string $format): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function open(SalesChannelContext $context)
    {
        return $this->decorated->open($context);
    }

    /**
     * @inheritDoc
     */
    public function registerModel(array $model): void
    {
        $this->decorated->registerModel($model);
    }

    /**
     * Resolves the SeoRoutes before passing to the decorated service
     *
     * @param resource $handle
     * @param ExportItem[] $items
     */
    public function writeList($handle, array $items): void
    {
        foreach ($items as $item) {
            foreach ($item->getParams() as $key => $value) {
                if (!is_string($value)) {
                    continue;
                }

                $value = str_replace(
                    [PHP_EOL, ' '],
                    [' ', ''],
                    $value
                );
                $item->set($key, $value);
            }
        }

        $this->decorated->writeList($handle, $items);
    }

    /**
     * Clears the seo generation context for the next write process
     *
     * @param SyncProfileEntity $syncProfile
     * @param SalesChannelContext $context
     * @param resource $handle
     */
    public function close(SyncProfileEntity $syncProfile, SalesChannelContext $context, $handle): void
    {
        $this->decorated->close($syncProfile, $context, $handle);
    }

    /**
     * Clears the seo generation content for the next write process
     *
     * @param resource $handle
     */
    public function abort($handle): void
    {
        $this->decorated->abort($handle);
    }
}