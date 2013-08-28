<?php

namespace Application\Model;

use \PDO;

class SubmissionService
{
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getSubmissionData($name) {
        $stmt = $this->db->prepare('
            SELECT * FROM submission s WHERE s.name = ?
            ');
        $stmt->execute(array($name));
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (count($rows) > 0) {
            return $rows[0];
        } else {
            return null;
        }
    }

}
