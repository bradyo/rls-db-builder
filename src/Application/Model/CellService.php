<?php

namespace Application\Model;

use Application\Model\Cell;
use Application\Model\Citation;
use \PDO;

class CellService
{
    private $db;
    private $cache;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * @param Cell $cell
     */
    public function save($cell) {
        // save cell
        $cellParams = array(
            'experiment_id' => $cell->getExperiment()->getId(),
            'label' => $cell->getLabel(),
            'strain_id' => $cell->getStrain()->getId(),
            'media' => $cell->getMedia(),
            'temperature' => $cell->getTemperature(),
            'lifespan' => $cell->getLifespan(),
            'end_state' => $cell->getEndState(),
        );
        $id = $this->insert($cellParams);
        $cell->setId($id);

        // save cell citation relationships
        $citations = $cell->getCitations();
        foreach ($citations as $citation) {
            $this->insertCellCitation($cell->getId(), $citation->getId());
        }
    }

    public function insert($data) {
        // get (or create) prepared statemenet for insert
        $insertStmt = $this->cache['cellInsertStmt'];
        if ($insertStmt === null) {
            $columns = array(
                'experiment_id',
                'label',
                'strain_id',
                'media',
                'temperature',
                'lifespan',
                'end_state',
            );
            $columnsString = join(',', $columns);
            $valuesString = join(',', array_fill(0, count($columns), '?'));
            $sql = "INSERT INTO cell ({$columnsString}) VALUES ({$valuesString})";
            $insertStmt = $this->db->prepare($sql);
            $this->cache['cellInsertStmt'] = $insertStmt;
        }
        // execute insert statement
        $params = array(
            $data['experiment_id'],
            $data['label'],
            $data['strain_id'],
            $data['media'],
            $data['temperature'],
            $data['lifespan'],
            $data['end_state']
        );
        $insertStmt->execute($params);
        return $this->db->lastInsertId();
    }

    public function insertCellCitation($cellId, $citationId) {
        $stmt = null;
        if (isset($this->cache['cellCitationInsertStmt'])) {
            $stmt = $this->cache['cellCitationInsertStmt'];
        } else {
            $stmt = $this->db->prepare('
                INSERT INTO cell_citation (cell_id, citation_id)  VALUES (?, ?)
            ');
            $this->cache['cellCitationInsertStmt'] = $stmt;
        }
        $stmt->execute(array($cellId, $citationId));
    }


}
