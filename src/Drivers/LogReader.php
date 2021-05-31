<?php

declare(strict_types=1);

namespace Pollen\Parser\Drivers;

use Pollen\Parser\AbstractReader;
use Pollen\Parser\FileParserInterface;
use Pollen\Parser\ReaderInterface;

/**
 * USAGE :
 * ---------------------------------------------------------------------------------------------------------------------
 use Pollen\Parser\Driver\LogReader as Reader;

 $reader = Reader::createFromPath(
    VENDOR_PATH . '/presstify-plugins/parser/Resources/sample/sample.log', [
    'offset'        => 1,
    'primary'       => 'lastname',
    'page'          => 1,
    'per_page'      => -1
 ]);

 // Lancement de la récupération des éléments.
 // @var \Pollen\Parser\Driver\LogReader
 $reader->fetch();

 // Récupération du tableau de la liste des éléments courants.
 // @var array
 $reader->all();

 // Récupération de la liste des complète des enregistrements.
 // @var array
 $reader->getRecords();

 // Récupération la liste des éléments de la page 2.
 // @var array
 $reader->fetchForPage(2)->all();

 // Récupération du nombre total de resultats.
 // @var int
 $reader->getTotal();

 // Récupération du numéro de la dernière de page.
 // @var int
 $reader->getLastPage();

 // Récupération du nombre d'éléments courants.
 // @var int
 $reader->getCount();
 */
class LogReader extends AbstractReader implements LogReaderInterface
{
    /**
     * {@inheritDoc}
     *
     * @return LogReaderInterface
     */
    public static function createFromPath(string $path, array $params = [], ...$args): ReaderInterface
    {
        return (new static((new LogFileParser($args))->setSource($path)))->setParams($params);
    }
}