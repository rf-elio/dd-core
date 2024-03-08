<?php
/**
 * Copyright (c) 2024, elio GmbH.
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

namespace Elio\ElioSearch\Command;

use Elio\ElioSearch\Core\Sync\Util\CategoryUtil;
use Elio\ElioSearch\ElioSearch;
use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\Content\Category\CategoryEntity;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CategoryExcludingInheritanceCommand
 *
 * @category Shopware
 * @author Andrei Baev <anb@elio-systems.com>
 * @author elio GmbH <support@elio-systems.com>
 * @copyright Copyright (c) 2024, elio GmbH (https://www.elio-systems.com)
 */
class ExcludingCategoryInheritanceCommand extends Command
{
    public function __construct(
        private readonly EntityRepository $categoryRepository,
        private readonly LoggerInterface  $logger
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('elio-search:exclusion:category-inheritance:update');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = Context::createDefaultContext();
        try {
            $this->updateExcludeInheritance($context);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'line' => $e->getLine()
            ]);
            $output->writeln('<error>' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
        return Command::SUCCESS;
    }

    private function updateExcludeInheritance(Context $context): void
    {
        $criteria = new Criteria();
        $categories = $this->categoryRepository->search($criteria, $context);
        $result = CategoryUtil::buildCustomFieldInheritanceForCategories($categories);

        if (array_key_exists('customFields', $result) && !empty($result['customFields'])) {
            $dataToUpdate = [];
            foreach ($result['customFields'] as $categoryId => $customFields) {
                /** @var CategoryEntity $category */
                $category = $categories->get($categoryId);
                $actualCustomFields = $category->getCustomFields();
                // Inherit only this field. For safety reasons, we don't inherit the whole custom fields
                $actualCustomFields[ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_PARENTAL_EXCLUDE] = $customFields[ElioSearch::CUSTOM_FIELD_CONTENT_EXPORT_PARENTAL_EXCLUDE];
                $dataToUpdate[] = [
                    'id' => $categoryId,
                    'customFields' => $actualCustomFields
                ];
            }
            if (!empty($dataToUpdate)) {
                $this->categoryRepository->update($dataToUpdate, $context);
            }
        }
    }
}