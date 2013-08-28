<?php

namespace Application\Model;

use Application\Model\Strain;
use \PDO;

class StrainService
{
    /**
     * @var PDO
     */
    private $db;
    private $cache;

    public function __construct($db) {
        $this->db = $db;
    }

    public function findOneByName($name) {
        $row = $this->fetchOneByName($name);
        if ($row === null) {
            return null;
        }
        $strain = new Strain();
        $strain->setId($row['id'] );
        $strain->setName( $row['name'] );
        $strain->setBackground( $row['background'] );
        $strain->setMatingType( $row['mating_type'] );
        $strain->setGenotype( $row['genotype'] );
        $strain->setShortGenotype( $row['short_genotype'] );
        $strain->setPoolingGenotype( $row['pooling_genotype'] );
        $strain->setComment( $row['comment'] );
        $strain->setContactEmail( $row['contact_email'] );
        return $strain;
    }

    public function fetchOneByName($name) {
        $stmt = null;
        if (isset($this->cache['findStmt'])) {
            $stmt = $this->cache['findStmt'];
        } else {
            $stmt = $this->db->prepare('
                SELECT * FROM strain
                WHERE name = ?
                LIMIT 1
            ');
            $this->cache['findStmt'] = $stmt;
        }
        $stmt->execute(array($name));

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($results) > 0) {
            $row = $results[0];
            return $row;
        } else {
            return null;
        }
    }

    public function fetchAllIndexedByName($strainNames) {
        if (count($strainNames) < 1) {
            return array();
        }
        $qString = join(',', array_fill(0, count($strainNames), '?'));
        $stmt = $this->db->prepare('
            SELECT * FROM strain
            WHERE name IN (' . $qString . ')
            ');
        $params = array_merge($strainNames);
        $stmt->execute($params);

        $data = array();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $name = $row['name'];
            $data[$name] = $row;
        }
        return $data;
    }

    public function insertStrain($strainData) {
        // get (or create) prepared statemenet for insert
        $insertStmt = $this->cache['strainInsertStmt'];
        if ($insertStmt == null) {
            $columns = array(
                'name',
                'contact_email',
                'background',
                'mating_type',
                'genotype',
                'short_genotype',
                'pooling_genotype',
                'comment'
            );
            $columnsString = join(',', $columns);
            $valuesString = join(',', array_fill(0, count($columns), '?'));
            $sql = "INSERT INTO strain ({$columnsString}) VALUES ({$valuesString})";
            $insertStmt = $this->db->prepare($sql);
            $this->cache['strainInsertStmt'] = $insertStmt;
        }
        // execute insert statement
        $params = array(
            $strainData['name'],
            $strainData['contact_email'],
            $strainData['background'],
            $strainData['mating_type'],
            $strainData['genotype'],
            $strainData['short_genotype'],
            $strainData['pooling_genotype'],
            $strainData['comment'],
        );
        $insertStmt->execute($params);
        return $this->db->lastInsertId();
    }

    /**
     * @param Strain $strain
     */
    public function save($strain) {
        $data = array(
            'name' => $strain->getName(),
            'contact_email' => $strain->getContactEmail(),
            'background' => $strain->getBackground(),
            'mating_type' => $strain->getMatingType(),
            'genotype' => $strain->getGenotype() ,
            'short_genotype' => $strain->getShortGenotype(),
            'pooling_genotype' => $strain->getPoolingGenotype(),
            'comment' => $strain->getComment()
        );
        $id = $this->insertStrain($data);
        $strain->setId($id);
    }
}
