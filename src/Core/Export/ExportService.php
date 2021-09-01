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

namespace Elio\FactFinder\Core\Export;


use DateTime;
use Elio\FactFinder\Core\Export\Exception\ExportNotSupportedException;
use Elio\FactFinder\Core\Export\Generator\ExportGeneratorInterface;
use Elio\FactFinder\Core\Export\Writer\FileWriterInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Aggregate\SalesChannelDomain\SalesChannelDomainEntity;
use Shopware\Core\System\SalesChannel\Context\AbstractSalesChannelContextFactory;
use Shopware\Core\System\SalesChannel\Context\SalesChannelContextService;
use Throwable;

/**
 * Class ExportService
 * @package Elio\FactFinder\Core\Export
 * @category  Shopware
 * @author    elio GmbH <support@elio-systems.com>
 * @author    Ralf Frommherz <rf@elio-systems.com>
 * @copyright Copyright (c) 2021, elio GmbH (https://www.elio-systems.com)
 */
class ExportService
{
    private EntityRepositoryInterface $exportRepository;
    private iterable $generators;
    private LoggerInterface $logger;
    private iterable $writers;
    private AbstractSalesChannelContextFactory $salesChannelContextFactory;

    /**
     * ExportService constructor.
     * @param EntityRepositoryInterface $exportRepository
     * @param AbstractSalesChannelContextFactory $salesChannelContextFactory
     * @param LoggerInterface $logger
     * @param ExportGeneratorInterface[] $generators
     * @param FileWriterInterface[] $writers
     */
    public function __construct(
        EntityRepositoryInterface $exportRepository,
        AbstractSalesChannelContextFactory $salesChannelContextFactory,
        LoggerInterface $logger,
        iterable $generators,
        iterable $writers
    )
    {
        $this->exportRepository = $exportRepository;
        $this->salesChannelContextFactory = $salesChannelContextFactory;
        $this->logger = $logger;
        $this->generators = $generators;
        $this->writers = $writers;
    }

    /**
     * Searches all due exports
     *
     * @param Context $context
     * @return EntityCollection
     */
    public function getDueExports(Context $context) : EntityCollection
    {
        // @todo: implement due feature
        $criteria = new Criteria();
        $criteria->addAssociation('salesChannel.domains');
        $criteria->addFilter(new EqualsFilter('active', true));
        return $this->exportRepository->search($criteria, $context)->getEntities();
    }

    /**
     * Generates the export
     */
    public function generate(ExportEntity $export, Context $context) : void
    {
        $generator = $this->getGenerator($export);
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

        $languageIds = $salesChannel->getDomains()->map(function (SalesChannelDomainEntity $salesChannelDomain) {
            return $salesChannelDomain->getLanguageId();
        });

        $this->exportRepository->update([['id' => $export->getId(), 'lastGenerationStartedAt' => new DateTime()]], $context);

        foreach ($languageIds as $languageId) {
            $salesChannelContext = $this->salesChannelContextFactory->create('', $salesChannel->getId(), [SalesChannelContextService::LANGUAGE_ID => $languageId]);
            $this->logger->info(
                sprintf('Generating export: %s', $export->getName()),
                ['id' => $export->getId(), 'salesChannelId' => $salesChannel->getId(), 'salesChannelName' => $salesChannel->getName(), 'language' => $languageId]
            );

            $stream = new OutputStream($writer, $export, $salesChannelContext);
            $stream->open();
            try {
                $generator->generate($export, $stream, $salesChannelContext);
                $stream->close();
            } catch (Throwable $ex) {
                $stream->abort();
                $this->logger->error($ex->getMessage());
            }
        }

        $this->exportRepository->update([['id' => $export->getId(), 'lastGenerationFinishedAt' => new DateTime()]], $context);
    }

    /**
     * Gets the matching generator for the given export
     *
     * @param ExportEntity $export
     * @return ExportGeneratorInterface
     * @throw ExportNotSupportedException Will be thrown if no matching genereator is present
     */
    protected function getGenerator(ExportEntity $export) : ExportGeneratorInterface
    {
        foreach ($this->generators as $generator) {
            if($generator->supports($export)) {
                return $generator;
            }
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