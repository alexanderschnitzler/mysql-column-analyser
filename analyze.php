<?php
declare(strict_types=1);

use Schnitzler\MysqlColumnAnalyzer\Command\AnalyseCommand;
use Symfony\Component\Console\Application;

require_once __DIR__ . '/vendor/autoload.php';

$application = new Application('schnitzler/mysql-column-analyser', '1.0.0');
$application->add(new AnalyseCommand());
$application->setDefaultCommand('analyze', true);
$application->run();
