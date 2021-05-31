<?php

declare(strict_types=1);

namespace Pollen\Parser;

use Exception;
use Illuminate\Support\Collection as LaraCollection;

abstract class AbstractReader implements ReaderInterface
{
    /**
     * Instance du jeu de résultat courant.
     * @var LaraCollection|null
     */
    protected $chunks;

    /**
     * Instance de la classe de traitement du fichier source.
     * @var FileParser|null
     */
    protected $parser;

    /**
     * Colonne de clés primaires d'indexation des éléments.
     * @var string|int|null
     */
    protected $primary;

    /**
     * Instance du jeu de résultat complet.
     * @var LaraCollection|null
     */
    protected $records;

    /**
     * @param FileParserInterface $parser
     */
    public function __construct(FileParserInterface $parser)
    {
        $this->setParser($parser);
    }

    /**
     * @inheritDoc
     */
    abstract public static function createFromPath(string $path, array $params = [], ...$args): ReaderInterface;

    /**
     * @inheritDoc
     */
    public function fetch(): ReaderInterface
    {
        $this->fetchRecords();
        $this->fetchItems();

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetchItems(): ReaderInterface
    {
        $this->items = [];

        $per_page = $this->getPerPage();
        $page = $this->getCurrentPage();
        $total = $this->getTotal();
        $offset = $this->getOffset();
        $records = clone $this->getRecords();

        $this->chunks = $records->splice($offset);
        $this->chunks = $this->chunks->forPage($page, $per_page);
        if ($this->hasPrimary()) {
            $this->chunks = $this->chunks->keyBy($this->getPrimary());
        }

        $this->setCount($this->chunks->count());
        $this->setLastPage($per_page > -1 ? (int)ceil($total / $per_page) : 1);

        $this->set($this->chunks->all());

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetchForPage(int $page = 1): ReaderInterface
    {
        if ($this->page !== $page) {
            $this->setCurrentPage($page > 0 ? $page : 1);
            $this->fetch();
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function fetchRecords(): ReaderInterface
    {
        if (is_null($this->records)) {
            try {
                $this->getParser()->parse();
            } catch (Exception $e) {

            }
            $this->records = $this->getParser()->collect();

            $this->setTotal($this->records->count() - $this->getOffset());
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasPrimary(): bool
    {
        return (bool)$this->primary;
    }

    /**
     * @inheritDoc
     */
    public function getParser(): FileParserInterface
    {
        return $this->parser;
    }

    /**
     * @inheritDoc
     */
    public function getPrimary()
    {
        return $this->primary;
    }

    /**
     * @inheritDoc
     */
    public function getRecords(): ?LaraCollection
    {
        return $this->records;
    }

    /**
     * @inheritDoc
     */
    public function setParams(array $params = []): ReaderInterface
    {
        foreach ($params as $key => $param) {
            switch ($key) {
                case 'offset' :
                    $this->setOffset($param);
                    break;
                case 'page' :
                    $this->setCurrentPage($param);
                    break;
                case 'per_page' :
                    $this->setPerPage($param);
                    break;
                case 'primary' :
                    $this->setPrimary($param);
                    break;
                /**
                 * @todo
                case 'orderby' :
                break;
                case 'search' :
                break;
                 */
            }
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setParser(FileParserInterface $parser): ReaderInterface
    {
        $this->parser = $parser;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function setPrimary($primary): ReaderInterface
    {
        if (is_numeric($primary)) {
            $this->primary = (int)$primary;
        } elseif (is_string($primary)) {
            $this->primary = $primary;
        } else {
            $this->primary = $primary;
        }

        return $this;
    }

    /**
     * @inheritDoc
     *
     * @throws Exception
     */
    public function toArray(): array
    {
        try {
            return $this->all() ?: [];
        } catch (Exception $e) {
            return [];
        }
    }
}