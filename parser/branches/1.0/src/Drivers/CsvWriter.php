<?php

declare(strict_types=1);

namespace Pollen\Parser\Drivers;

use Exception;
use League\Csv\CannotInsertRecord;
use League\Csv\CharsetConverter;
use League\Csv\ColumnConsistency;
use League\Csv\Exception as LeagueCsvException;
use League\Csv\Writer as LeagueCsvWriter;
use Pollen\Parser\Exceptions\WriterException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

/**
 *  USAGE :
 * ---------------------------------------------------------------------------------------------------------------------
 * use Pollen\Parser\Driver\CsvWriter
 *
 * // Initialisation
 * - (string) path : Le chemin vers le fichier csv, mettre à null pour traiter la création du csv en mémoire.
 * - (array) params :
 *    > (int) checker : Permet de forcer à 2, le nombre de cellules par ajout de ligne de données. true|false aussi
 * accepté.
 *    ...
 *      > (string[]) errors['not_empty'] : Message d'erreur lancé lorsque l'une des lignes de données ajoutées contient
 * une valeur vide.
 *      > (callable[]) formatters : Formatte les valeurs des lignes de données ajoutées en mininuscule, a l'exception
 * de la première lettre.
 *      > (callable[]) validators['not_empty'] : Vérifie qu'aucune valeur de lignes de données ne soit vide
 * - (string) mode : 'a+' > mode d'écriture inclusif. @see https://www.php.net/manual/fr/function.fopen.php
 * $csv = CsvWriter::createFromPath('/example.csv', [
 * 'checker'       => 2,
 * 'delimiter'     => ',',
 * 'enclosure'     => '"',
 * 'escape'        => '\\',
 * 'errors'        => [
 * 'not_empty' => 'Toutes les valeurs n\'ont pas été renseignée dans l\'enregistrement : %s'
 * ],
 * 'formatters' => [
 * function (array $row) {
 * return array_map('ucfirst', array_map('strtolower', $row));
 * }
 * ],
 * 'validators' => [
 * 'not_empty' => function (array $row) {
 * foreach ($row as $value) {
 * if (empty($value)) {
 * return false;
 * }
 * }
 * return true;
 * }
 * ]
 * ], 'a+');
 *
 * // Ajout d'une ligne de données.
 * $csv->addRow(['freDdy', 'merCurY']);
 *
 * // Ajout de plusieurs lignes de données.
 * $csv->addRows([
 * ['roGer', 'taYlor'],
 * ['brIAn', 'mAy'],
 * ['joHN', 'deACon']
 * ]);
 *
 * // Génération de la réponse HTTP.
 * $csv->response('queen-members.csv');
 *
 * // Génération de la réponse HTTP de téléchargement.
 * $csv->download('queen-members.csv');
 */
class CsvWriter implements CsvWriterInterface
{
    /**
     * Indicateur d'intégrité du controleur.
     * @var boolean
     */
    private $_prepared = false;

    /**
     * Instance du controleur de traitement.
     * @var LeagueCsvWriter
     */
    private $_writer;

    /**
     * Indicateur d'activation du vérificateur d'intégrité du nombre de colonnes.
     * {@internal Permet de contrôler que toutes les enregistrements ajouté contiennent le même nombre de colonnes.
     *  - true Active la fonctionnalité sur la base du nombre de colonnes du premier enregistrement.
     *  - false Désactivation de la fonctionnalité.
     *  - int Nombre de colonne à contraindre.
     * }
     * @var boolean|int
     */
    protected $checker = true;

    /**
     * Caractère de délimitation des colonnes.
     * @var string
     */
    protected $delimiter = ',';

    /**
     * Attribut d'encodage en entrée et en sortie.
     * @var string[]
     */
    protected $encoding = [];

    /**
     * Caractère d'encapsulation des données.
     * @var string
     */
    protected $enclosure = '"';

    /**
     * Caractère d'échappemment.
     * @var string
     */
    protected $escape = '\\';

    /**
     * Cartographie des messages d'erreur.
     * @var array
     */
    protected $errors = [];

    /**
     * Cartographie de formateurs de données.
     * {@internal Tableau indexés de fonctions de rappel.}
     * @return callable[]
     */
    protected $formatters = [];

    /**
     * Cartographie de formateurs de données.
     * {@internal Tableau associatif de fonctions de rappel.
     * Si le retour de la fonction de rappel est false, une exception est lancée.
     * L'indice de qualification peut être utilisé pour personnaliser les messages d'erreurs.}
     * @return callable[][]
     */
    protected $validators = [];

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        try {
            return $this->getWriter()->toString();
        } catch(LeagueCsvException $e) {
            throw new WriterException($e->getMessage(), 0, $e);
        }
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public static function createFromPath(?string $path = null, array $params = [], ...$args): CsvWriterInterface
    {
        array_unshift($args, $path ?? 'php://temp');

        return (new static())->prepare(LeagueCsvWriter::createFromPath(...$args), $params);
    }

    /**
     * @inheritDoc
     */
    public function addRow(array $line): CsvWriterInterface
    {
        try {
            $this->getWriter()->insertOne($line);
        } catch (CannotInsertRecord $e) {
            try {
                $record = json_encode($e->getRecord(), JSON_THROW_ON_ERROR);
            } catch (Throwable $e) {
                $record = "{}";
            }

            throw new WriterException(
                sprintf($this->errors[$e->getName()] ?? $e->getMessage(), $record)
            );
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addRows($lines): CsvWriterInterface
    {
        try {
            foreach ($lines as $line) {
                $this->getWriter()->insertOne($line);
            }
        } catch (CannotInsertRecord $e) {
            try {
                $record = json_encode($e->getRecord(), JSON_THROW_ON_ERROR);
            } catch (Throwable $e) {
                $record = "{}";
            }

            throw new WriterException(
                sprintf($this->errors[$e->getName()] ?? $e->getMessage(), $record)
            );
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function download(string $name = 'file.csv', array $headers = []): StreamedResponse
    {
        return $this->response($name, $headers, 'attachment');
    }

    /**
     * Récupération de l'instance du controleur de traitement.
     *
     * @return LeagueCsvWriter
     */
    public function getWriter(): LeagueCsvWriter
    {
        return $this->_writer;
    }

    /**
     * @inheritDoc
     */
    public function setEncoding(array $encoding): CsvWriterInterface
    {
        $this->encoding = [$encoding[0] ?? 'utf-8', $encoding[1] ?? 'utf-8'];

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setErrors(array $errors = []): CsvWriterInterface
    {
        if (!isset($errors['_checker'])) {
            $errors['_checker'] = 'Invalid column number for record [%s].';
        }
        $this->errors = $errors;

        return $this;
    }

    /**
     * Préparation.
     *
     * @param LeagueCsvWriter $writer
     * @param array $params Liste des paramètres.
     *
     * @return $this
     *
     * @throws Exception
     */
    protected function prepare(LeagueCsvWriter $writer, array $params = []): CsvWriter
    {
        if (!$this->_prepared) {
            $this->_writer = $writer;

            foreach ($params as $key => $value) {
                switch ($key) {
                    case 'checker' :
                        $this->checker = $value;
                        break;
                    case 'delimiter' :
                        $this->delimiter = $value;
                        break;
                    case 'encoding' :
                        $this->setEncoding($value);
                        break;
                    case 'enclosure' :
                        $this->enclosure = $value;
                        break;
                    case 'escape' :
                        $this->escape = $value;
                        break;
                    case 'formatters' :
                        $this->formatters = $value;
                        break;
                    case 'validators' :
                        $this->validators = $value;
                        break;
                }
            }
            $this->setErrors($params['errors'] ?? []);
            if ($this->formatters) {
                array_map(
                    function ($formatter) {
                        $this->_writer->addFormatter($formatter);
                    },
                    $this->formatters
                );
            }
            if ($this->validators) {
                array_walk(
                    $this->validators,
                    function ($validator, $name) {
                        $this->_writer->addValidator($validator, (string)$name);
                    }
                );
            }

            $this->_writer->setDelimiter($this->delimiter)->setEnclosure($this->enclosure)->setEscape($this->escape);

            if ($this->encoding) {
                CharsetConverter::addTo($this->_writer, $this->encoding[0], $this->encoding[1]);
            }

            if ($this->checker) {
                $this->_writer->addValidator(
                    new ColumnConsistency(is_bool($this->checker) ? -1 : $this->checker),
                    '_checker'
                );
            }

            $this->_prepared = true;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function response(
        string $name = 'file.csv',
        array $headers = [],
        string $disposition = 'inline'
    ): StreamedResponse {
        $response = new StreamedResponse(
            function () {
                $flush_threshold = $this->getWriter()->getFlushThreshold() ?: 1000;

                foreach ($this->getWriter()->chunk(1024) as $offset => $chunk) {
                    echo $chunk;

                    if ($offset % $flush_threshold === 0) {
                        flush();
                    }
                }
            }
        );

        $disposition = $response->headers->makeDisposition($disposition, $name);

        $response->headers->replace(
            array_merge(
                $headers,
                [
                    'Content-Encoding'    => 'none',
                    'Content-Type'        => 'text/csv; charset=UTF-8',
                    'Content-Disposition' => $disposition,
                    'Content-Description' => 'File Transfer',
                ]
            )
        );

        return $response->send();
    }
}