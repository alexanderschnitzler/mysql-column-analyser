<?php
declare(strict_types=1);

namespace Schnitzler\MysqlColumnAnalyzer\ColumnTypeProcessor;

class StringProcessor implements ColumnTypeProcessorInterface
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return 'string';
    }

    /**
     * @param array $columnTypes
     * @param array $columnValues
     * @return mixed|void
     */
    public function process(array $columnTypes, array $columnValues)
    {
        reset($columnTypes);
        $count = count($columnTypes);

        $this->checkIfValuesAreOnlyIntegers($columnValues);
    }

    /**
     * @param array $columnValues
     */
    private function checkIfValuesAreOnlyIntegers(array $columnValues)
    {
        $foundNonIntegerValue = false;

        foreach ($columnValues as $columnValue) {
            if ($columnValue === '') {
                continue;
            }

            if ($columnValue === (string)(int)$columnValue) {
                continue;
            }

            $foundNonIntegerValue = true;
        }

        if (!$foundNonIntegerValue) {
            throw new \LogicException('Column is of type ' . $this->getType() . ' but it only holds integers');
        }
    }
}
