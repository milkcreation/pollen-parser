<?php

declare(strict_types=1);

namespace Pollen\Parser\Drivers;

use Pollen\Parser\FileParserInterface;
use Pollen\Parser\ReaderInterface;

interface LogReaderInterface extends ReaderInterface
{
    /**
     * {@inheritDoc}
     *
     * @return LogFileParserInterface
     */
    public function getParser(): FileParserInterface;

    /**
     * {@inheritDoc}
     *
     * @return LogReaderInterface
     */
    public static function createFromPath(string $path, array $params = [], ...$args): ReaderInterface;
}