<?php
declare(strict_types=1);

namespace Schnitzler\MysqlColumnAnalyzer\Command;

use Doctrine\DBAL\Configuration;
use Schnitzler\MysqlColumnAnalyzer\ColumnTypeProcessor\StringProcessor;
use Schnitzler\MysqlColumnAnalyzer\ColumnTypeProcessor\TextProcessor;
use Schnitzler\MysqlColumnAnalyzer\ColumnTypeProcessorRegistry;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;

class AnalyseCommand extends Command
{
    protected static $defaultName = 'analyze';

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $databaseConnection;

    protected function configure()
    {
        $this
            ->addOption('table', 't', InputOption::VALUE_OPTIONAL)
            ->addOption('column', 'c', InputOption::VALUE_OPTIONAL)
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Doctrine\DBAL\DBALException
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);

        $dotEnvFile = __DIR__ . '/../../.env';

        if (!file_exists($dotEnvFile)) {
            trigger_error('.env file is missing.', E_ERROR);
        }

        $dotenv = new Dotenv();
        $dotenv->load($dotEnvFile);

        $config = new Configuration();
        $connectionParams = [
            'url' => $_ENV['DB_URL'],
        ];
        $this->databaseConnection = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Doctrine\DBAL\DBALException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $onlyTable = $input->getOption('table');
        $onlyTable = $onlyTable === null ? null : trim($onlyTable);
        $onlyTable = $onlyTable === '' ? null : $onlyTable;

        $onlyColumn = $input->getOption('column');
        $onlyColumn = $onlyColumn === null ? null : trim($onlyColumn);
        $onlyColumn = $onlyColumn === '' ? null : $onlyColumn;

        //ColumnTypeProcessorRegistry::register(new BooleanProcessor());
        //ColumnTypeProcessorRegistry::register(new IntegerProcessor());
        ColumnTypeProcessorRegistry::register(new TextProcessor());
        ColumnTypeProcessorRegistry::register(new StringProcessor());

        if ($onlyTable) {
            $tableNames = [$onlyTable];
        } else {
            $tableNames = $this->databaseConnection->getSchemaManager()->listTableNames();
        }

        foreach ($tableNames as $tableName) {
            if ($onlyTable === null &&
                (
                    strpos($tableName, 'be_') === 0
                    || strpos($tableName, 'cf_') === 0
                    || strpos($tableName, 'fe_') === 0
                    || strpos($tableName, 'cache_') === 0
                    || strpos($tableName, 'export_') === 0
                    || strpos($tableName, 'sys_') === 0
                    || strpos($tableName, 'temp_') === 0
                    || strpos($tableName, 'zzz_') === 0
                )
            ) {
                $output->writeln('<info>Ignore table ' . $tableName . '</info>');
                continue;
            }

            $output->writeln([
                '<info>' . $tableName . '</info>',
                str_repeat('=', mb_strlen($tableName)),
                '',
            ]);

            $columns = $this->databaseConnection->getSchemaManager()->listTableColumns($tableName);
            if ($onlyColumn) {
                $columns = array_filter($columns, function ($key) use ($onlyColumn) {
                    return $key === $onlyColumn;
                }, ARRAY_FILTER_USE_KEY);
            }

            $renderTable = false;
            $tableOutput = new Table($output);
            $tableOutput->setHeaders(['Column', 'Status']);

            foreach ($columns as $columnName => $column) {
                if (strpos($columnName, 'zzz_') === 0) {
                    continue;
                }

                $outputColumnName = $columnName;

                $definedColumnType = $column->getType()->getName();

                $actualColumnTypes = [];
                $columnValues = $this->databaseConnection->query('SELECT DISTINCT(' . $columnName . ') FROM ' . $tableName)->fetchAll(\Doctrine\DBAL\FetchMode::COLUMN);
                $columnValues = array_unique($columnValues);

                foreach ($columnValues as $columnValue) {
                    $actualColumnTypes[] = gettype($columnValue);
                }

                $actualColumnTypes = array_unique($actualColumnTypes);

                try {
                    $processor = ColumnTypeProcessorRegistry::getProcessor($definedColumnType);
                    $processor->process($actualColumnTypes, $columnValues);
                } catch (\LogicException $e) {
                    $renderTable = true;
                    $outputRow = [];
                    $outputRow[] = '<error>' . $outputColumnName . '</error>';
                    $outputRow[] = '<error>' . $e->getMessage() . '</error>';
                    $tableOutput->addRow($outputRow);
                } catch (\Throwable $e) {
                }
            }

            if ($renderTable) {
                $tableOutput->render();
                $output->writeln('');
            }
        }
    }
}
