<?php

declare(strict_types=1);

namespace Pollen\Parser;

use Illuminate\Support\Collection as LaraCollection;

interface ReaderInterface
{
    /**
     * Création d'une instance basé sur un chemin.
     *
     * @param string $path Chemin vers le fichier à traiter.
     * @param array $params Liste des paramètres de configuration.
     * @param array $args Liste des arguments dynamiques fopen. Seuls mode et context sont permis.
     *
     * @return static
     */
    public static function createFromPath(string $path, array $params = [], ...$args): ReaderInterface;

    /**
     * Récupération de la liste des éléments courant.
     *
     * @return static
     */
    public function fetch(): ReaderInterface;

    /**
     * Récupération de la liste des éléments courant associé à une page.
     *
     * @param int $page
     *
     * @return static
     */
    public function fetchForPage(int $page = 1): ReaderInterface;

    /**
     * Récupération de la liste complète des enregistrements.
     *
     * @return static
     */
    public function fetchRecords(): ReaderInterface;

    /**
     * Vérification d'existance d'une colonne de clés primaires d'indexation des éléments.
     *
     * @return boolean
     */
    public function hasPrimary(): bool;

    /**
     * Récupération de l'instance du controleur de traitement.
     *
     * @return FileParserInterface
     */
    public function getParser(): FileParserInterface;

    /**
     * Récupération de la colonne de clés primaires d'indexation des éléments.
     *
     * @return int|string|null
     */
    public function getPrimary();

    /**
     * Récupération de la liste complète des enregistrements.
     *
     * @return LaraCollection|null
     */
    public function getRecords(): ?LaraCollection;

    /**
     * Définition de la liste des paramètres.
     *
     * @param array $params Liste des paramètres.
     *
     * @return static
     */
    public function setParams(array $params = []): ReaderInterface;

    /**
     * Définition de l'instance de la classe de traitement du fichier source.
     *
     * @param FileParserInterface $parser
     *
     * @return static
     */
    public function setParser(FileParserInterface $parser): ReaderInterface;

    /**
     * Définition de la colonne de clé primaire.
     *
     * @param string|int $primary Indice ou Nom de qualification de la colonne de clé primaire.
     *
     * @return static
     */
    public function setPrimary($primary): ReaderInterface;

    /**
     * Récupération de la liste des éléments sous forme de tableau.
     *
     * @return array
     */
    public function toArray(): array;
}