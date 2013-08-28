<?php

namespace Application\Model;

use Application\Debug;
use Application\Model\Sample;
use Application\QueryBuilder;
use \PDO;

class SampleService
{
    private $db;
    private $cache;

    public function __construct($db) {
        $this->db = $db;
        $this->cellService = new CellService($db);
    }

    private function createQueryBuilder($params = array()) {
        $queryBuilder = new QueryBuilder();
        $queryBuilder->setSelect('sample.*');
        $queryBuilder->setFrom('
            sample
            LEFT JOIN sample_cell ON sample_cell.sample_id = sample.id
            LEFT JOIN cell ON cell.id = sample_cell.cell_id
            LEFT JOIN experiment ON experiment.id = cell.experiment_id
            LEFT JOIN strain ON strain.id = cell.strain_id
            LEFT JOIN cell_citation ON cell_citation.cell_id = cell.id
            LEFT JOIN citation ON citation.id = cell_citation.citation_id
            ');
        if (isset($params['pooled_by'])) {
            $queryBuilder->addWhere('sample.pooled_by = ?', array($params['pooled_by']));
        }
        if (isset($params['label'])) {
            $queryBuilder->addWhere('sample.label = ?', array($params['label']));
        }
        if (isset($params['experiment.name'])) {
            $queryBuilder->addWhere('experiment.name = ?', array($params['experiment.name']));
        }
        $queryBuilder->setGroupBy('sample.id');
        return $queryBuilder;
    }

    public function fetchCount($params = array())  {
        $queryBuilder = $this->createQueryBuilder($params);
        $queryBuilder->setSelect("COUNT(1)");

        $stmt = $this->db->prepare($queryBuilder->getSql());
        $stmt->execute($queryBuilder->getParameters());
        return $stmt->fetchColumn();
    }

    public function fetchAll($params = array(), $orderBy = null, $limit = null, $offset = null)  {
        $queryBuilder = $this->createQueryBuilder($params);
        $queryBuilder->setOrderBy($orderBy);
        $queryBuilder->setLimit($limit);
        $queryBuilder->setOffset($offset);

        $stmt = $this->db->prepare($queryBuilder->getSql());
        $stmt->execute($queryBuilder->getParameters());

        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if ($results) {
            return $results;
        } else {
            return array();
        }
    }

    public function fetchOne($params = array()) {
        $queryBuilder = $this->createQueryBuilder($params);
        $queryBuilder->setLimit(1);

        $stmt = $this->db->prepare($queryBuilder->getSql());
        $stmt->execute($queryBuilder->getParameters());

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) > 0) {
            return $rows[0];
        } else {
            return null;
        }
    }

    public function insertSample($data) {
        // get (or create) prepared statemenet for insert
        $insertStmt = $this->cache['sampleInsertStmt'];
        if ($insertStmt == null) {
            $columns = array(
                'pooled_by',
                'label',
                'strain_name',
                'background',
                'mating_type',
                'genotype',
                'media',
                'temperature',
                'lifespans',
                'lifespans_count',
                'lifespans_omitted_count',
                'lifespans_mean',
                'lifespans_median',
                'lifespans_stdev',
                'end_states',
            );
            $columnsString = join(',', $columns);
            $valuesString = join(',', array_fill(0, count($columns), '?'));
            $sql = "INSERT INTO sample ({$columnsString}) VALUES ({$valuesString})";
            $insertStmt = $this->db->prepare($sql);
            $this->cache['sampleInsertStmt'] = $insertStmt;
        }
        // execute insert statement
        $params = array(
            $data['pooled_by'],
            $data['label'],
            $data['strain_name'],
            $data['background'],
            $data['mating_type'],
            $data['genotype'],
            $data['media'],
            $data['temperature'],
            $data['lifespans'],
            $data['lifespans_count'],
            $data['lifespans_omitted_count'],
            $data['lifespans_mean'],
            $data['lifespans_median'],
            $data['lifespans_stdev'],
            $data['end_states'],
        );
        $insertStmt->execute($params);
        return $this->db->lastInsertId();
    }

    public function insertSampleCell($sampleId, $cellId) {
        $stmt = null;
        if (! isset($this->cache['sampleCellInsertStmt'])) {
            $stmt = $this->db->prepare('
                INSERT INTO sample_cell (sample_id, cell_id)  VALUES (?, ?)
            ');
            $this->cache['sampleCellInsertStmt'] = $stmt;
        } else {
            $stmt = $this->cache['sampleCellInsertStmt'];
        }
        $stmt->execute(array($sampleId, $cellId));
    }

    /**
     * @param Sample $sample
     */
    public function save($sample) {
        // save sample
        $sampleParams = array(
            'pooled_by' => $sample->getPooledBy(),
            'label' => $sample->getLabel(),
            'strain_name' => $sample->getStrainName(),
            'background' => $sample->getBackground(),
            'mating_type' => $sample->getMatingType(),
            'genotype' => $sample->getGenotype(),
            'media' => $sample->getMedia(),
            'temperature' => $sample->getTemperature(),
            'lifespans' => join(',', $sample->getLifespans()),
            'lifespans_mean' => $sample->getLifespansMean(),
            'lifespans_median' => $sample->getLifespansMedian(),
            'lifespans_stdev' => $sample->getLifespansStdev(),
            'lifespans_count' => $sample->getLifespansCount(),
            'lifespans_omitted_count' => $sample->getLifespansOmittedCount(),
            'end_states' => join(',', $sample->getEndStates()),
        );
        $id = $this->insertSample($sampleParams);
        $sample->setId($id);

        // save all sample cells
        $cells = $sample->getCells();
        foreach ($cells as $cell) {
            if ($cell->getId() === null) {
                $this->cellService->saveCell($cell);
            }
            $this->insertSampleCell($sample->getId(), $cell->getId());
        }
    }
}
