<?php

namespace Application\Build;

class ExperimentFileCombiner
{
    /**
     * @param array $rows array of ExperimentFileRow objects to pool
     * @return array of combined ExperimentFileRow objects
     */
    public function combine($rows) {
        // we need to save row id to combined row id mappings so we can convert
        // reference row ids to combined row ids
        $combinedRowIds = array(); // row id => combined row id
        $referenceRowIds = array(); // combined id => reference row ids (uncombined)

        // combine rows where matching
        $combinedRowsByKey = array();
        $combinedRowId = 1;
        foreach ($rows as $row /* @var $row ExperimentFileRow */) {
            $combinedRow = null; /* @var $combinedRow ExperimentFileRow */
            $combineKey = $this->getPoolingKey($row);
            if (! isset($combinedRowsByKey[$combineKey])) {
                // create new combined row
                $combinedRow = new ExperimentFileRow();
                $combinedRow->setId($combinedRowId);
                $combinedRow->setLabel($row->getLabel());
                $combinedRow->setStrainName($row->getStrainName());
                $combinedRow->setMedia($row->getMedia());
                $combinedRow->setTemperature($row->getTemperature());
                $combinedRow->setLifespans($row->getLifespans());
                $combinedRow->setEndStates($row->getEndStates());

                $combinedRowsByKey[$combineKey] = $combinedRow;
                $combinedRowId++;
            } else {
                // append data to existing combined row
                $combinedRow = $combinedRowsByKey[$combineKey];
                $combinedRow->setLifespans(
                    array_merge($combinedRow->getLifespans(), $row->getLifespans())
                );
                $combinedRow->setEndStates(
                    array_merge($combinedRow->getEndStates(), $row->getEndStates())
                );
            }

            // save row id to combined row id mappings so we can convert
            // reference row ids to combined row ids
            $combinedRowIds[$row->getId()] = $combinedRow->getId();
            $referenceRowIds[$combinedRow->getId()] = $row->getReferenceIds();
        }

        // Set combined reference row ids based on un-combined row id mappings
        $combinedRows = array_values($combinedRowsByKey);
        foreach ($combinedRows as $combinedRow) {
            $combinedReferenceIds = array();
            foreach ($referenceRowIds[$combinedRow->getId()] as $referenceRowId) {
                $referenceId = $combinedRowIds[$referenceRowId];
                if (! in_array($referenceId, $combinedReferenceIds)) {
                    $combinedReferenceIds[] = $referenceId;
                }
            }
            $combinedRow->setReferenceIds($combinedReferenceIds);
        }
        return $combinedRows;
    }

    /**
     * @param ExperimentFileRow $row
     * @return string unique key string to combine rows by
     */
    private function getPoolingKey($row) {
        return join('/', array(
            $row->getLabel(),
            $row->getStrainName(),
            $row->getMedia(),
            $row->getTemperature(),
        ));
    }
}
