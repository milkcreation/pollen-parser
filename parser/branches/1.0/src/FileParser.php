<?php

declare(strict_types=1);

namespace Pollen\Parser;

use Illuminate\Support\Collection;
use Pollen\Parser\Exceptions\FileParserException;
use Pollen\Parser\Exceptions\UnableOpenFileException;
use Throwable;

class FileParser implements FileParserInterface
{
    /**
     * Liste des arguments de traitement complémentaires.
     * @var array
     */
    protected $args = [];

    /**
     * Liste des enregistrements du fichier.
     * @var array
     */
    public $records = [];

    /**
     * @var resource|null
     */
    protected $stream;

    /**
     * Fichier source de la liste des enregistrements.
     * @var string
     */
    protected $source = '';

    /**
     * @param array $args
     *
     * @return void
     */
    public function __construct(array $args)
    {
        $this->args = $args;
    }

    /**
     * @inheritDoc
     */
    public function collect(): Collection
    {
        return new Collection($this->records);
    }

    /**
     * @inheritDoc
     */
    public function open()
    {
        $stream = @fopen($this->source, 'rb');
        if (false === $stream) {
            throw new UnableOpenFileException(
                sprintf('FileParser unable to open source [%s]', $this->source)
            );
        }

        return $stream;
    }

    /**
     * @inheritDoc
     */
    public function parse(): FileParserInterface
    {
        try {
            $this->stream = $this->open();
        } catch (Throwable $e) {
            throw new FileParserException($e->getMessage(), 0, $e);
        }

        if (!fclose($this->stream)) {
            throw new FileParserException();
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setSource(string $source): FileParserInterface
    {
        $this->source = $source;

        return $this;
    }
}