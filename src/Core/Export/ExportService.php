<?php
/**
 * Copyright (c) 2021, elio GmbH.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 */

namespace Elio\ElioSearch\Core\Export;

use Cron\CronExpression;
use Cron\FieldFactory;
use DateTime;
use Elio\ElioBatteryIncludedSearchExtension\Core\Export\Generator\BatteryIncluded\ProductExportGenerator;
use Elio\ElioBatteryIncludedSearchExtension\Core\Export\Writer\BatteryIncludedWriter;
use Elio\ElioSearch\Core\Export\Exception\ExportNotSupportedException;
use Elio\ElioSearch\Core\Export\Generator\ExportGeneratorInterface;
use Elio\ElioSearch\Core\Export\Writer\FileWriterInterface;
use Elio\ElioSearch\Core\Export\Event\ExportGeneratedEvent;
use Exception;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Psr\EventDispatcher\EventDispatcherInterface;
use Throwable;

/**
 * Class ExportService
 * @package Elio\ElioSearch\Core\Export
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ExportService
{
    /**
     * @var EntityRepository
     */
    private EntityRepository $exportRepository;
    /**
     * @var ExportGeneratorInterface[]
     */
    private iterable $generators;
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;
    /**
     * @var FileWriterInterface[]
     */
    private iterable $writers;
    /**
     * @var AbstractSalesChannelContextFactory
     */
    private AbstractSalesChannelContextFactory $salesChannelContextFactory;
    /**
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * ExportService constructor.
     * @param EntityRepository $exportRepository
     * @param AbstractSalesChannelContextFactory $salesChannelContextFactory
     * @param EventDispatcherInterface $eventDispatcher
     * @param LoggerInterface $logger
     * @param ExportGeneratorInterface[] $generators
     * @param FileWriterInterface[] $writers
     */
    public function __construct(
        EntityRepository $exportRepository,
        AbstractSalesChannelContextFactory $salesChannelContextFactory,
        EventDispatcherInterface $eventDispatcher,
        LoggerInterface $logger,
        iterable $generators,
        iterable $writers
    )
    {
        $this->exportRepository = $exportRepository;
        $this->salesChannelContextFactory = $salesChannelContextFactory;
        $this->eventDispatcher = $eventDispatcher;
        $this->logger = $logger;
        $this->generators = $generators;
        $this->writers = $writers;
    }

    /**
     * Searches for exports based in the given criteria. Only active exports included.
     *
     * @param Criteria $criteria
     * @param Context $context
     * @return EntityCollection<ExportEntity>
     */
    public function getExports(Criteria $criteria, Context $context): EntityCollection
    {
        $criteria->addAssociation('salesChannel.domains');
        $criteria->addFilter(new EqualsFilter('active', true));
        /* @phpstan-ignore-next-line */
        return $this->exportRepository->search($criteria, $context)->getEntities();
    }

    /**
     * Searches all due exports
     *
     * @param Criteria $criteria
     * @param Context $context
     * @return EntityCollection<ExportEntity>
     * @throws Exception
     */
    public function getDueExports(Criteria $criteria, Context $context) : EntityCollection
    {
        $now = new DateTime('now');

        /** @var ExportEntity[] $exports */
        $exports = $this->getExports($criteria, $context);
        $dueExports = new EntityCollection();
        foreach ($exports as $export) {
            if (!$export->getNextGenerationDueAt() || $export->getNextGenerationDueAt() <= $now) {
                $dueExports->add($export);
            }
        }

        return $dueExports;
    }

    /**
     * Generates the export
     * @throws Exception
     */
    public function generate(ExportEntity $export, Context $context) : void
    {
        $generators = $this->getGenerators($export);
        $writer = $this->getWriter($export);
        $salesChannel = $export->getSalesChannel();

        if(!$salesChannel || !$salesChannel->getDomains()) {
            $this->logger->info(
                'Cannot generate product export: association "salesChannel.domains" is missing',
                ['id' => $export->getId()]
            );

            throw new RuntimeException(sprintf(
                'Cannot generate product export "%s": association "salesChannel.domains" is missing',
                $export->getName()
            ));
        }

        $languageId = $export->getLanguageId();
        $salesChannelContext = $this->salesChannelContextFactory->create('', $salesChannel->getId(), [SalesChannelContextService::LANGUAGE_ID => $languageId]);

        $this->exportRepository->update([['id' => $export->getId(), 'lastGenerationStartedAt' => new DateTime()]], $context);
        $this->logger->info(
            sprintf('Generating export: %s', $export->getName()),
            ['id' => $export->getId(), 'salesChannelId' => $salesChannel->getId(), 'salesChannelName' => $salesChannel->getName(), 'language' => $languageId]
        );

        $stream = new OutputStream($writer, $export, $salesChannelContext);
        $stream->open($salesChannelContext);

        foreach ($generators as $generator) {
            $stream->registerModel($generator->getModel($export));
        }

        try {
            foreach ($generators as $generator) {
                $generator->generate($export, $stream, $salesChannelContext);
            }
            $stream->close();

            $this->eventDispatcher->dispatch(new ExportGeneratedEvent($export, $salesChannelContext));

        } catch (Throwable $ex) {
            $stream->abort();
            $this->logger->error($ex->getMessage());
        }

        $cron = new CronExpression($export->getInterval(), new FieldFactory());
        $this->exportRepository->update([[
            'id' => $export->getId(),
            'lastGenerationFinishedAt' => new DateTime(),
            'nextGenerationDueAt' => $cron->getNextRunDate()->format('Y-m-d H:i:s')
        ]], $context);
    }

    /**
     * Gets the matching generators for the given export
     *
     * @param ExportEntity $export
     * @return array<ExportGeneratorInterface>
     * @throw ExportNotSupportedException Will be thrown if no matching genereator is present
     */
    protected function getGenerators(ExportEntity $export) : array
    {
        $generators = [];
        foreach ($this->generators as $generator) {
            // TODO: remove testings code
            if ($generator instanceof ProductExportGenerator) {
                return [$generator];
            }

            continue;

            if($generator->supports($export)) {
                $generators[] = $generator;
            }
        }

        if(!empty($generators)) {
            return $generators;
        }

        throw new ExportNotSupportedException(sprintf(
            'Export "%s" with type "%s" is not supported by any of the registered generators',
            $export->getName(), $export->getType()
        ));
    }

    /**
     * Fetches the matching file writer for the given export
     *
     * @param ExportEntity $export
     * @return FileWriterInterface
     */
    protected function getWriter(ExportEntity $export) : FileWriterInterface
    {
        foreach ($this->writers as $writer) {
            // TODO: remove testings code
            if ($writer instanceof BatteryIncludedWriter) {
                return $writer;
            }

            continue;

            if($writer->supports($export)) {
                return $writer;
            }
        }

        throw new ExportNotSupportedException(sprintf(
            'Export "%s" with format "%s" is not supported by any of the registered file writers',
            $export->getName(), $export->getFormat()
        ));
    }
}
