<?php

namespace Application\Model;

use Application\Model\Experiment;
use \PDO;

class ExperimentService 
{
    const OFFICIAL_NAMESPACE = 'official';
    const OFFICIAL_CONTACT_EMAIL = 'admin@sageweb.org';
    
    private $db;
    
    private $cache;

    /**
     * @param PDO $db
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * @param Experiment $experiment 
     */
    public function saveExperiment($experiment) {
       $id = $this->insertExperiment(
            $experiment->getNamespace(), 
            $experiment->getFilename()
        );
       $experiment->setId($id);
    }
    
    public function insertExperiment($filename) {
        $stmt = $this->cache['insertExperimentStmt'];
        if ($stmt === null) {
            $columns = array(
                'contact_email',
                'name',
            );
            $columnsString = join(',', $columns);
            $valuesString = join(',', array_fill(0, count($columns), '?'));
            $sql = "INSERT INTO experiment ({$columnsString}) VALUES ({$valuesString})";
            $stmt = $this->db->prepare($sql);
            $this->cache['insertExperimentStmt'] = $stmt;
        }
        // execute insert statement
        $params = array(
            self::OFFICIAL_CONTACT_EMAIL,
            $filename,
        );
        $stmt->execute($params);
        return $this->db->lastInsertId();
    }
}
