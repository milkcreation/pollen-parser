<?php

declare(strict_types=1);

namespace Pollen\Parser\Drivers;

use Pollen\Parser\FileParserInterface;

interface LogFileParserInterface extends FileParserInterface
{
    /**
     * Traitement d'une ligne.
     *
     * @param string $line
     *
     * @return array
     */
    public function parseOneLine(string $line): array;
}