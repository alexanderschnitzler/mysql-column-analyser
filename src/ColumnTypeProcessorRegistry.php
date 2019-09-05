<?php
declare(strict_types=1);

namespace Schnitzler\MysqlColumnAnalyzer;

use Schnitzler\MysqlColumnAnalyzer\ColumnTypeProcessor\ColumnTypeProcessorInterface;

class ColumnTypeProcessorRegistry
{
    private static $stack;

    /**
     * @param ColumnTypeProcessorInterface $columnTypeProcessor
     */
    public static function register(ColumnTypeProcessorInterface $columnTypeProcessor)
    {
        $type = $columnTypeProcessor->getType();

        if (isset(static::$stack[$type])) {
            throw new \InvalidArgumentException(
                'There is already a processor registered for type '. $type,
                1567693027
            );
        }

        static::$stack[$type] = $columnTypeProcessor;
    }

    /**
     * @param string $type
     * @return ColumnTypeProcessorInterface
     */
    public static function getProcessor(string $type): ColumnTypeProcessorInterface
    {
        if (!isset(static::$stack[$type])) {
            throw new \RuntimeException(
                'There is no processor registered for type '. $type,
                1567693076
            );
        }

        return static::$stack[$type];
    }

    private function __construct()
    {
    }
}
