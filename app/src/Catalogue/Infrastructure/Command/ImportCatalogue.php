<?php

declare(strict_types=1);

namespace App\Catalogue\Infrastructure\Command;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import-catalogue', description: 'Import catalogue data.')]
class ImportCatalogue extends Command
{
    public function __construct(private ManagerRegistry $registry)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('Import data by providing CSV file. Input takes hedear names or column numbers, from which data comes from.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;

        // or return this to indicate incorrect command usage; e.g. invalid options
        // or missing arguments (it's equivalent to returning int(2))
        // return Command::INVALID
    }
}
