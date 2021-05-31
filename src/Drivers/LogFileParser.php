<?php

declare(strict_types=1);

namespace Pollen\Parser\Drivers;

use Exception;
use Pollen\Parser\Exceptions\FileParserException;
use SplFileObject;
use Pollen\Parser\FileParser;
use Pollen\Parser\FileParserInterface;
use Throwable;

class LogFileParser extends FileParser implements LogFileParserInterface
{
    /**
     * Motif de traitement des éléments d'une ligne
     * @var string
     */
    protected $pattern = '/\[(?P<date>.*)\]\s(?P<logger>.*)\.(?P<level>\w+)\:\s(?P<message>[^\[\{]+)\s(?P<context>[\[\{].*[\]\}])\s(?P<extra>[\[\{].*[\]\}])/';

    /**
     * @inheritDoc
     */
    public function parse(): FileParserInterface
    {
        try {
            $file = new SplFileObject($this->source, 'r');

            while (!$file->eof()) {
                if ($line = $this->parseOneLine($file->current())) {
                    $this->records[] = $line;
                }
                $file->next();
            }
        } catch (Exception $e) {
            throw new FileParserException($e->getMessage(), 0, $e);
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function parseOneLine(string $line): array
    {
        if (($line !== '') && preg_match($this->pattern, $line, $data)) {
            try {
                $context = json_decode($data['context'], true, 512, JSON_THROW_ON_ERROR);
            } catch (Throwable $e) {
                $context = [];
            }

            try {
                $extra = json_decode($data['extra'], true, 512, JSON_THROW_ON_ERROR);
            } catch (Throwable $e) {
                $extra = [];
            }

            return [
                'date'    => $data['date'],
                'logger'  => $data['logger'],
                'level'   => $data['level'],
                'message' => $data['message'],
                'context' => $context,
                'extra'   => $extra
            ];
        }

        return [];
    }
}