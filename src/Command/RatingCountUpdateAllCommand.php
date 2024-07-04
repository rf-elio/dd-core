<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Command;

use Elio\ElioDataDiscovery\Core\Sync\RatingCountService;
use Exception;
use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RatingCountUpdateAllCommand extends Command
{
    public function __construct(
        private readonly RatingCountService $ratingCountService,
        private readonly LoggerInterface $logger
    )
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('elio-data-discovery:rating-count:update');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $context = Context::createDefaultContext();
        try {
            $this->ratingCountService->updateAllProductRatingCounts($context);
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
}
