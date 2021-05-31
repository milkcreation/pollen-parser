<?php

declare(strict_types=1);

namespace Pollen\Parser\Drivers;

use Pollen\Parser\FileParserInterface;
use Pollen\Parser\ReaderInterface;

interface JsonReaderInterface extends ReaderInterface
{
    /**
     * {@inheritDoc}
     *
     * @return JsonFileParserInterface
     */
    public function getParser(): FileParserInterface;

    /**
     * {@inheritDoc}
     *
     * @return JsonReaderInterface
     */
    public static function createFromPath(string $path, array $params = [], ...$args): ReaderInterface;
}