<?php
declare(strict_types=1);

namespace Schnitzler\MysqlColumnAnalyzer\ColumnTypeProcessor;

class IntegerProcessor implements ColumnTypeProcessorInterface
{
    /**
     * @return string
     */
    public function getType(): string
    {
        return 'integer';
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

        if ($count === 0) {
            return;
        } elseif ($count === 1) {
            $columnType = current($columnTypes);
            if ($columnType !== $this->getType()) {
                dump($columnType);
            }
        } else {
            dump($columnTypes);
        }
    }
}
