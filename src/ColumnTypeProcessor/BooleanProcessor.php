<?php
declare(strict_types=1);

namespace Schnitzler\MysqlColumnAnalyzer\ColumnTypeProcessor;

class BooleanProcessor implements ColumnTypeProcessorInterface
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return 'boolean';
    }

    /**
     * @param array $columnTypes
     * @param array $columnValues
     */
    public function process(array $columnTypes, array $columnValues)
    {
        reset($columnTypes);
        $count = count($columnTypes);

        if ($count === 0) {
            return;
        } elseif ($count === 1) {
            $columnType = current($columnTypes);

            if ($columnType === $this->getType()) {
                // all fine
                return;
            }

            $diff = array_diff($columnValues, [0,1]);
            if (empty($diff)) {
                // integers used as boolean, all fine
                return;
            }

            trigger_error(
                'Column is of type ' . $this->getType() . ' but it holds non fitting values ' . implode(',', $diff),
                E_USER_WARNING
            );
        } else {
            dump($columnTypes);
        }
    }
}
