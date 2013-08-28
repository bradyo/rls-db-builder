<?php

namespace Application\Model;

use \PDO;
use Application\QueryBuilder;

class ComparisonService
{
    const DEFAULT_SORT_FIELD = 'id';
    const DEFAULT_SORT_ORDER = 'asc';

    private $db;

    private $cache;

    public function __construct($db) {
        $this->db = $db;
    }

    private function createQueryBuilder($params = array()) {
        // build up the search query
        $queryBuilder = new QueryBuilder();
        $queryBuilder->setSelect('
            comparison.id,
            test_sample.pooled_by as test_sample_pooled_by,
            test_sample.label as test_sample_label,
            test_sample.strain_name as test_sample_strain_name,
            test_sample.background as test_sample_background,
            test_sample.mating_type as test_sample_mating_type,
            test_sample.genotype as test_sample_genotype,
            test_sample.media as test_sample_media,
            test_sample.temperature as test_sample_temperature,
            test_sample.lifespans as test_sample_lifespans,
            test_sample.lifespans_count as test_sample_lifespans_count,
            test_sample.lifespans_omitted_count as test_sample_lifespans_omitted_count,
            test_sample.lifespans_mean as test_sample_lifespans_mean,
            test_sample.lifespans_median as test_sample_lifespans_median,
            test_sample.lifespans_stdev as test_sample_lifespans_stdev,
            test_sample.end_states as test_sample_end_states,
            reference_sample.pooled_by as reference_sample_pooled_by,
            reference_sample.label as reference_sample_label,
            reference_sample.strain_name as reference_sample_strain_name,
            reference_sample.background as reference_sample_background,
            reference_sample.mating_type as reference_sample_mating_type,
            reference_sample.genotype as reference_sample_genotype,
            reference_sample.media as reference_sample_media,
            reference_sample.temperature as reference_sample_temperature,
            reference_sample.lifespans as reference_sample_lifespans,
            reference_sample.lifespans_count as reference_sample_lifespans_count,
            reference_sample.lifespans_omitted_count as reference_sample_lifespans_omitted_count,
            reference_sample.lifespans_mean as reference_sample_lifespans_mean,
            reference_sample.lifespans_median as reference_sample_lifespans_median,
            reference_sample.lifespans_stdev as reference_sample_lifespans_stdev,
            reference_sample.end_states as reference_sample_end_states,
            comparison.percent_change,
            comparison.ranksum_u,
            comparison.ranksum_p
        ');
        $queryBuilder->setFrom('
            comparison
            LEFT JOIN sample test_sample
                ON comparison.test_sample_id = test_sample.id
            LEFT JOIN sample reference_sample
                ON comparison.reference_sample_id = reference_sample.id
            ');
        if (isset($params['test_sample_pooled_by'])) {
            $queryBuilder->addWhere('test_sample.pooled_by = ?', array($params['test_sample_pooled_by']));
        }
        return $queryBuilder;
    }

    public function fetchCount($params = array())  {
        $queryBuilder = $this->createQueryBuilder($params);
        $queryBuilder->setSelect("COUNT(1)");

        /* @var PDOStatement $stmt */
        $stmt = $this->db->prepare($queryBuilder->getSql());
        $stmt->execute($queryBuilder->getParameters());
        return $stmt->fetchColumn();
    }

    public function fetchAll($params = array(), $orderBy = null, $limit = null, $offset = null)  {
        $queryBuilder = $this->createQueryBuilder($params);
        $queryBuilder->setOrderBy($this->getCleanedOrderBy($orderBy));
        $queryBuilder->setLimit($limit);
        $queryBuilder->setOffset($offset);

        $stmt = $this->db->prepare($queryBuilder->getSql());
        $stmt->execute($queryBuilder->getParameters());
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function getAllowedSortFields() {
        return array(
            'id',
            'percent_change',
            'ranksum_p',
        );
    }

    private function getCleanedOrderBy($orderBy) {
        $parts = explode(' ', $orderBy);
        $sortField = self::DEFAULT_SORT_FIELD;
        if (isset($parts[0])) {
            if (in_array($parts[0], $this->getAllowedSortFields())) {
                $sortField = $parts[0];
            }
        }
        $sortOrder = 'asc';
        if (isset($parts[1])) {
            if (in_array(strtolower($parts[1]), array('asc', 'dec'))) {
                $sortOrder = $parts[1];
            }
        }
        return $sortField . ' ' . $sortOrder;
    }

    public function fetchOne($params = array()) {
        $queryBuilder = $this->createQueryBuilder($params);
        $queryBuilder->setLimit(1);

        $stmt = $this->db->prepare($queryBuilder->getSql());
        $stmt->execute($queryBuilder->getParameters());
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function insertComparison($data) {
        // get (or create) prepared statemenet for insert
        $insertStmt = $this->cache['comparisonInsertStmt'];
        if ($insertStmt == null) {
            $columns = array(
                'pooled_by',
                'test_sample_id',
                'reference_sample_id',
                'percent_change',
                'ranksum_u',
                'ranksum_p',
            );
            $columnsString = join(',', $columns);
            $valuesString = join(',', array_fill(0, count($columns), '?'));
            $sql = "INSERT INTO comparison ({$columnsString}) VALUES ({$valuesString})";
            $insertStmt = $this->db->prepare($sql);
            $this->cache['comparisonInsertStmt'] = $insertStmt;
        }
        // execute insert statement
        $params = array(
            $data['pooled_by'],
            $data['test_sample_id'],
            $data['reference_sample_id'],
            $data['percent_change'],
            $data['ranksum_u'],
            $data['ranksum_p'],
        );
        $insertStmt->execute($params);
        return $this->db->lastInsertId();
    }

    /**
     * @param Comparison $comparison
     */
    public function save($comparison) {
        $data = array(
            'pooled_by' => $this->getPooledByValue($comparison),
            'test_sample_id' => $comparison->getTestSample()->getId(),
            'reference_sample_id' => $comparison->getReferenceSample()->getId(),
            'percent_change' => $comparison->getPercentChange(),
            'ranksum_u' => $comparison->getRanksumU(),
            'ranksum_p' => $comparison->getRanksumP(),
        );
        $id = $this->insertComparison($data);
        $comparison->setId($id);
    }

    private function getPooledByValue(Comparison $comparison) {
        if ($comparison->getTestSample()->getPooledBy() === $comparison->getReferenceSample()->getPooledBy()) {
            return $comparison->getTestSample()->getPooledBy();
        } else {
            return null;
        }
    }
}
