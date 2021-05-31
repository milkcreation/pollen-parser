<?php

declare(strict_types=1);

namespace Pollen\Parser\Drivers;

use Pollen\Parser\FileParserInterface;

interface CsvFileParserInterface extends FileParserInterface
{
    /**
     * Définition du caractère de délimitation des colonnes.
     *
     * @param string $delimiter
     *
     * @return static
     */
    public function setDelimiter(string $delimiter): CsvFileParserInterface;

    /**
     * Définition de la convertion d'encodage des résultats.
     *
     * @param array $encoding {
     *      @type string $input Encodage à l'entrée.
     *      @type string $output Encodage à la sortie.
     * }
     *
     * @return static
     */
    public function setEncoding(array $encoding): CsvFileParserInterface;

    /**
     * Définition du caractère d'encapsulation des données.
     *
     * @param string $enclosure
     *
     * @return static
     */
    public function setEnclosure(string $enclosure): CsvFileParserInterface;

    /**
     * Définition du caractère d'échappemment des données.
     *
     * @param string $escape
     *
     * @return static
     */
    public function setEscape(string $escape): CsvFileParserInterface;

    /**
     * Définition de l'activation de l'entête.
     *
     * @param int|false $header Activation|indice de la ligne d'enregistrement.
     *
     * @return static
     */
    public function setHeader($header): CsvFileParserInterface;
}