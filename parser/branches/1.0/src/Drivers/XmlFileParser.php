<?php

declare(strict_types=1);

namespace Pollen\Parser\Drivers;

use Exception;
use Pollen\Parser\Exceptions\FileParserException;
use Pollen\Parser\Lib\Xml2Assoc;
use Pollen\Parser\FileParserInterface;
use Pollen\Parser\FileParser;

class XmlFileParser extends FileParser implements XmlFileParserInterface
{
    /**
     * @inheritDoc
     */
    public function parse(): FileParserInterface
    {
        try {
            $this->stream = $this->open();
            $xml = (new Xml2Assoc())->parseFile($this->source, true);
            $xml = reset($xml);
            $this->records = $xml;
        } catch (Exception $e) {
            throw new FileParserException($e->getMessage(), 0, $e);
        }

        if( ! fclose($this->stream)){
            throw new FileParserException();
        }

        return $this;
    }
}