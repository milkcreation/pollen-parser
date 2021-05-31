<?php

declare(strict_types=1);

namespace Pollen\Parser\Drivers;

use Pollen\Parser\Exceptions\FileParserException;
use Pollen\Parser\FileParserInterface;
use Pollen\Parser\FileParser;
use JsonCollectionParser\Listener;
use JsonStreamingParser\Parser as StreamingParser;
use Throwable;

class JsonFileParser extends FileParser implements JsonFileParserInterface
{
    /**
     * @inheritDoc
     */
    public function parse(): FileParserInterface
    {
        try {
            $this->stream = $this->open();

            (new StreamingParser(
                $this->stream, new Listener(
                function (array $item) {
                    $this->records[] = $item;
                }, true
            )
            )
            )->parse();
        } catch (Throwable $e) {
            throw new FileParserException($e->getMessage(), 0, $e);
        }

        if (!fclose($this->stream)) {
            throw new FileParserException();
        }

        return $this;
    }
}