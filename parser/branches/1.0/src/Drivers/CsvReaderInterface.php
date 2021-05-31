<?php

declare(strict_types=1);

namespace Pollen\Parser\Drivers;

use Exception;
use Pollen\Parser\FileParserInterface;
use Pollen\Parser\ReaderInterface;

interface CsvReaderInterface extends ReaderInterface
{
    /**
     * {@inheritDoc}
     *
     * @return CsvFileParserInterface
     */
    public function getParser(): FileParserInterface;

    /**
     * {@inheritDoc}
     *
     * @return CsvReaderInterface
     *
     * @throws Exception
     */
    public static function createFromPath(string $path, array $params = [], ...$args): ReaderInterface;
}