<?php

namespace Application;

class QueryBuilder
{
    private $select;
    private $from;
    private $whereClauses;
    private $params;
    private $orderBy;
    private $groupBy;
    private $limit;
    private $offset;

    public function __construct() {
        $this->whereClauses = array();
        $this->params = array();
    }

    public function setSelect($select) {
        $this->select = $select;
    }

    public function setFrom($from) {
        $this->from = $from;
    }

    public function addWhere($whereClause, $params = null) {
        $this->whereClauses[] = $whereClause;
        if ($params !== null) {
            if (is_array($params)) {
                foreach ($params as $param) {
                    $this->params[] = $param;
                }
            } else {
                $this->params[] = $params;
            }
        }
    }

    public function setGroupBy($groupBy) {
        $this->groupBy = $groupBy;
    }

    public function setOrderBy($orderBy) {
        $this->orderBy = $orderBy;
    }

    public function setLimit($limit) {
        $this->limit = $limit;
    }

    public function setOffset($offset) {
        $this->offset = $offset;
    }

    public function getSql() {
        $query = 'SELECT ' . $this->select . ' FROM ' . $this->from;
        if (count($this->whereClauses) > 0) {
            $wrappedWhereClauses = array_map(
                function ($s) { return '(' . $s . ')'; },
                $this->whereClauses
            );
            $query .= ' WHERE ' . join(' AND ', $wrappedWhereClauses);
        }
        if ($this->groupBy) {
            $query .= ' GROUP BY ' . $this->groupBy;
        }
        if ($this->orderBy) {
            $query .= ' ORDER BY ' . $this->orderBy;
        }
        if ($this->limit) {
            $query .= ' LIMIT ' . intval($this->limit);
        }
        if ($this->offset) {
            $query .= ' OFFSET ' . intval($this->offset);
        }
        return $query;
    }

    public function getParameters() {
        return $this->params;
    }

}
