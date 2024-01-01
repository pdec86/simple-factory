<?php

declare(strict_types=1);

namespace App\Catalogue\Infrastructure\Command;

use App\Catalogue\Application\Model\ProductDTO;
use App\Catalogue\Application\Service\ProductManager;
use App\Catalogue\Domain\Model\Product;
use App\Catalogue\Domain\Service\CreateProductService;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:import-sample-products', description: 'Import sample catalogue data.')]
class ImportCatalogue extends Command
{
    private string $sampleProductsCSVPath;

    public function __construct(
        private ManagerRegistry $registry,
        string $sampleProductsCSVPath,
        private CreateProductService $createProductService
    ) {
        parent::__construct();
        $this->sampleProductsCSVPath = $sampleProductsCSVPath;
    }

    protected function configure(): void
    {
        $this->setHelp('Import data by providing CSV file. Input takes column number in CSV, from which data comes from.');
        $this->addArgument('nameColumnNo', InputArgument::REQUIRED, 'Product column number');
        $this->addArgument('csvSeparator', InputArgument::REQUIRED, 'CSV separator - one character');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Importing sample products names from "' . $this->sampleProductsCSVPath . '".');
        $nameColumnNo = (int) $input->getArgument('nameColumnNo');
        $csvSeparator = (string) trim($input->getArgument('csvSeparator'));

        if (1 !== strlen($csvSeparator)) {
            $output->writeln('CSV separator must be a single character.');
            return Command::INVALID;
        } else {
            $output->writeln('CSV separator: "' . $csvSeparator . '"');
        }

        $row = 0;
        $batchCount = 50;

        if (($handle = fopen($this->sampleProductsCSVPath, "r")) !== FALSE) {
            $entityManager = $this->registry->getManagerForClass(Product::class);

            try {
                while (($data = fgetcsv($handle, null, $csvSeparator)) !== false) {
                    $row++;
                    
                    if ($row === 1) {
                        // Header row
                        continue;
                    }

                    $productName = (string) filter_var($data[$nameColumnNo], FILTER_DEFAULT);
                    $product = $this->createProductService->execute($productName);
                    $entityManager->persist($product);

                    if ($row % $batchCount === 0) {
                        $entityManager->flush();
                        $entityManager->clear();
                    }
                }
            } catch (\Throwable $ex) {
                $output->writeln("Error {$ex->getMessage()} in file {$ex->getFile()} on line {$ex->getLine()}");
                return Command::FAILURE;
            } finally {
                $entityManager->flush();
                $entityManager->clear();
                fclose($handle);
                $output->writeln("Finishing import.");
            }
        }

        return Command::SUCCESS;

        // or return this if some error happened during the execution
        // (it's equivalent to returning int(1))
        // return Command::FAILURE;
    }
}
