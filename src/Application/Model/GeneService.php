<?php

namespace Application\Model;

use \PDO;

class GeneService
{
    private $db;

    /**
     * @param PDO $db
     */
    public function __construct($db) {
        $this->db = $db;
    }

    public function getNormalizedGenotype($input) {
        $genotype = trim($input);
        $values = str_replace('-', '', $genotype);
        $values = preg_split("/\s+/", $genotype);

        // using database, convert values making up genotype to a gene symbol
        $sth = $this->db->prepare('
            SELECT g.id, g.symbol FROM gene g
            LEFT JOIN gene_synonym s ON s.gene_id = g.id
            WHERE LOWER(g.symbol) = ? OR LOWER(g.locus_tag) = ?
        ');
        $normalizedValues = array();
        foreach ($values as $value) {
            // check case, if it is mixed just skip it
            $isUpper = false;
            if (strtoupper($value) == $value) {
                $isUpper = true;
            } else if (strtolower($value) == $value) {
                $isUpper = false;
            } else {
                // skip normalization on mixed cased values
                $normalizedValues[] = $value;
                continue;
            }
            $value = strtolower($value);

            // convert locus tag,
            $sth->execute(array($value, $value));
            $geneSymbols = array();
            $rows = $sth->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $row) {
                $geneSymbol = $row['symbol'];
                $geneSymbols[$geneSymbol] = $geneSymbol;
            }
            $geneSymbols = array_keys($geneSymbols);
            if (count($geneSymbols) == 1) {
                $geneSymbol = $geneSymbols[0];
                $value = $geneSymbol;
            }

            if ($isUpper) {
                $value = strtoupper($value);
            } else {
                $value = strtolower($value);
            }
            $normalizedValues[] = $value;
        }
        sort($normalizedValues);

        // reconstruct genotype string from normalized values
        $genotype = join(' ', array_values($normalizedValues));
        return $genotype;
    }

}
