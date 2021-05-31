<?php

declare(strict_types=1);

namespace Pollen\Parser;

use Illuminate\Support\Collection;
use Pollen\Parser\Exceptions\UnableOpenFileException;

interface FileParserInterface
{
    /**
     * Récupération d'une instance de la liste des enregistrements.
     *
     * @return Collection
     */
    public function collect(): Collection;

    /**
     * Récupération d'une instance de la liste des enregistrements.
     *
     * @return resource
     *
     * @throws UnableOpenFileException
     */
    public function open();

    /**
     * Définition du fichier source de récupération des enregistrements.
     *
     * @param string $source
     *
     * @return static
     */
    public function setSource(string $source): FileParserInterface;
}