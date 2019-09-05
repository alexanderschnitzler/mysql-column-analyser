<?php
declare(strict_types=1);

namespace Schnitzler\MysqlColumnAnalyzer\ColumnTypeProcessor;

interface ColumnTypeProcessorInterface
{
    /**
     * @return string
     */
    public function getType(): string;

    /**
     * @param array $columnTypes
     * @param array $columnValues
     * @return mixed
     */
    public function process(array $columnTypes, array $columnValues);
}
