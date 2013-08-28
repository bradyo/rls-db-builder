<?php
namespace Application\Model;

use Application\Model\Citation;
use \PDO;

class CitationService
{
    private $db;
    private $cache;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getCitationsIndexedByPubmedId() {
        $stmt = $this->db->prepare('SELECT * FROM citation');
        $stmt->execute();

        $citations = array();
        while (($row = $stmt->fetch(PDO::FETCH_ASSOC)) !== false) {
            $pubmedId = $row['pubmed_id'];
            $citations[$pubmedId] = $row;
        }
        return $citations;
    }

    public function findAllIndexedByPubmedId() {
        $citations = array();
        $citationRows = $this->getCitationsIndexedByPubmedId();
        foreach ($citationRows as $pubmedId => $citationRow) {
            $citation = $this->createCitationFromRow($citationRow);
            $citations[$pubmedId] = $citation;
        }
        return $citations;
    }

    public function findAll($criteria) {
        $params = array();
        $sql = 'SELECT * FROM citation';
        if (isset($criteria['pubmedIds'])) {
            $pubmedIds = $criteria['pubmedIds'];
            if (count($pubmedIds) < 1) {
                return array();
            }
            $qs = array_fill(0, count($pubmedIds), '?');
            $sql .= ' WHERE pubmed_id IN (' . join(',', $qs) . ')';
            $params = array_merge($params, $pubmedIds);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $citations = array();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $citations[] = $this->createCitationFromRow($row);
        }
        return $citations;
    }

    private function createCitationFromRow($row) {
        $citation = new Citation();
        $citation->setId($row['id']);
        $citation->setPubmedId($row['pubmed_id']);
        $citation->setTitle($row['title']);
        $citation->setAuthor($row['first_author']);
        $citation->setYear($row['year']);
        $citation->setSummary($row['summary']);
        return $citation;
    }

    public function insertCitationData($data) {
        // get (or create) prepared statemenet for insert
        $insertStmt = $this->cache['citationInsertStmt'];
        if ($insertStmt === null) {
            $columns = array(
                'pubmed_id',
                'title',
                'first_author',
                'year',
                'summary',
            );
            $columnsString = join(',', $columns);
            $valuesString = join(',', array_fill(0, count($columns), '?'));
            $sql = "INSERT INTO citation ({$columnsString}) VALUES ({$valuesString})";
            $insertStmt = $this->db->prepare($sql);
            $this->cache['citationInsertStmt'] = $insertStmt;
        }
        // execute insert statement
        $params = array(
            $data['pubmed_id'],
            $data['title'],
            $data['first_author'],
            $data['year'],
            $data['summary'],
        );
        $insertStmt->execute($params);
        return $this->db->lastInsertId();
    }

    /**
     * @param Citation $citation
     */
    public function save($citation) {
        $data = array(
            'pubmed_id' => $citation->getPubmedId(),
            'title' => $citation->getTitle(),
            'first_author' => $citation->getAuthor(),
            'year' => $citation->getYear(),
            'summary' => $citation->getSummary(),
        );
        $id = $this->insertCitationData($data);
        $citation->setId($id);
    }
}
