<?php

namespace Application\Build;

class ExperimentFileParser
{
    const DEFAULT_MEDIA = 'YPD';
    const DEFAULT_TEMPERATURE = 30;

    private $headerIndexes;
    private $rows;

    public function parse($filePath) {
        ini_set('auto_detect_line_endings', true);
        $file = fopen($filePath, 'r');

        // read header row and parse header indexes
        $headers = fgetcsv($file);
        $this->mapHeaderIndexes($headers);

        // parse data rows from file
        $rows = array();
        while (($columns = fgetcsv($file)) !== false) {
            $rowId = $this->parseRowId($columns);
            if (empty($rowId)) {
                continue;
            }
            $row = new ExperimentFileRow();
            $row->id = $rowId;
            $row->referenceIds = $this->parseReferenceRowIds($columns);
            $row->label = $this->parseLabel($columns);
            $row->strainName = $this->parseStrainName($columns);
            $row->media = $this->parseMedia($columns);
            $row->temperature = $this->parseTemperature($columns);
            $row->lifespans = $this->parseLifespans($columns);
            $row->endStates = $this->parseEndStates($columns, count($row->lifespans));
            $rows[] = $row;
        }
        $this->rows = $rows;
        fclose($file);
    }

    public function getRows() {
        return $this->rows;
    }

    private function mapHeaderIndexes($headerColumns) {
        // clean up headers
        $headerColumns = array_map('trim', $headerColumns);
        $headerColumns = array_map('lcfirst', $headerColumns);
        if (!isset($headerColumns['label']) && in_array('name', $headerColumns)) {
            $headerColumns['label'] = $headerColumns['name'];
        }

        $hasIdColumn = in_array('id', $headerColumns);
        $hasLabelColumn = in_array('label', $headerColumns);
        $hasLifespanColumn = in_array('lifespans', $headerColumns);
        if (! ($hasIdColumn && $hasLabelColumn && $hasLifespanColumn)) {
            throw new Exception('header requires "id", "name" and "lifespans" fields');
        }
        $this->headerIndexes = array_flip($headerColumns);
    }

    private function parseRowId($columns) {
        return trim($columns[$this->headerIndexes['id']]);
    }

    private function parseReferenceRowIds($columns) {
        $referenceRowIds = array();
        if (isset($this->headerIndexes['reference'])) {
            $index = $this->headerIndexes['reference'];
            $reference = $columns[$index];

            // convert to array and clean values
            $values = explode(',', $reference);
            $values = array_map('trim', $values);
            $values = array_map('intval', $values);
            foreach ($values as $value) {
                if ($value != 0) {
                    $referenceRowIds[] = $value;
                }
            }
        }
        return $referenceRowIds;
    }

    private function parseLabel($columns) {
        $index = $this->headerIndexes['label'];
        $label = $columns[$index];
        return $label;
    }

    private function parseStrainName($columns) {
        $strainName = null;
        if (isset($this->headerIndexes['strain'])) {
            $index = $this->headerIndexes['strain'];
            $value = (string) $columns[$index];
            $value = trim($value);

            // if strain looks like plate row well, convert to deletion collection id
            $matches = array();
            if (preg_match('/^(\d+)\s*([a-h])\s*(\d+)$/i', $value, $matches)) {
                $plate = $matches[1];
                $col = $matches[2];
                $well = $matches[3];
                $value = 'DC:' . $plate . $col . $well;
            }
            if (!empty($value)) {
                $strainName = $value;
            }
        }
        return $strainName;
    }

    private function parseMedia($columns) {
        $media = self::DEFAULT_MEDIA;
        if (isset($this->headerIndexes['media'])) {
            $value = (string) $columns[$this->headerIndexes['media']];
            $value = trim($value);
            if (!empty($value)) {
                $media = $value;
            }
        }
        return $media;
    }

    private function parseTemperature($columns) {
        // Clean up temperature a bit: remove trailing "C" and extra zeros.
        // Treat temperature value as a normalized string so that it pools
        // correctly.
        $temperature = self::DEFAULT_TEMPERATURE;
        if (isset($this->headerIndexes['temperature'])) {
            $value = $columns[$this->headerIndexes['temperature']];
            $value = strtolower($value);
            $value = trim($value);
            if (strstr('.', $value) !== false) {
                $value = rtrim($value, '0');
                $value = rtrim($value, '.');
            }
            if (!empty($value)) {
                $temperature = $value;
            }
        }
        return $temperature;
    }

    private function parseLifespans($columns) {
        // read lifespans up to endCode column if it exists
        $lifespans = array();
        if (isset($this->headerIndexes['endStates'])) {
            $lifespansColumn = $this->headerIndexes['lifespans'];
            $endCodeColumn = $this->headerIndexes['endStates'];
            $length = $endCodeColumn - $lifespansColumn;
            $lifespans = array_slice($columns, $lifespansColumn, $length);
        } else {
            $lifespans = array_slice($columns, $this->headerIndexes['lifespans']);
        }
        return $lifespans;
    }

    private function parseEndStates($columns, $count) {
        $endStates = array();
        if (isset($this->headerIndexes['endStates'])) {
            $endCodeColumn = $this->headerIndexes['endStates'];
            $endStates = array_slice($columns, $endCodeColumn, $count);
        } else {
            $endStates = array_fill(0, $count, null);
        }
        return $endStates;
    }

}
