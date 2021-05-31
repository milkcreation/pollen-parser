<?php

declare(strict_types=1);

namespace Pollen\Parser\Drivers;

use Pollen\Parser\FileParserInterface;
use Pollen\Parser\ReaderInterface;

interface XmlReaderInterface extends ReaderInterface
{
    /**
     * {@inheritDoc}
     *
     * @return XmlFileParserInterface
     */
    public function getParser(): FileParserInterface;

    /**
     * {@inheritDoc}
     *
     * @return XmlFileParserInterface
     */
    public static function createFromPath(string $path, array $params = [], ...$args): ReaderInterface;
}