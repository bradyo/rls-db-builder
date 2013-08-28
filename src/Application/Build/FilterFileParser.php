<?php

namespace Application\Build;

class FilterFileParser
{
    const SHORT_GENOTYPE_COLUMN = 0;
    const MEDIA_COLUMN = 1;
    const TEMPERATURE_COLUMN = 2;
    const BACKGROUND_COLUMN = 3;
    const MATING_TYPE_COLUMN = 4;
    const PUBMED_ID_COLUMN = 5;

    const DEFAULT_TEMPERATURE = 30.0;

    /**
     * @param string $filterPath Path to filters csv file
     * @return FilterFileRow[]
     */
    public function parseRows($filePath) {
        // read the file to extract filter information
        $file = fopen($filePath, 'r');
        fgetcsv($file); // discard header

        $rows = array();
        while (false !== ($columns = fgetcsv($file))) {
            if (count($columns) >= 5) {
                $row = new FilterFileRow();
                $row->setShortGenotype( trim($columns[self::SHORT_GENOTYPE_COLUMN]) );
                $row->setMedia( trim($columns[self::MEDIA_COLUMN]) );
                $row->setBackground( trim($columns[self::BACKGROUND_COLUMN]) );
                $row->setMatingType( trim($columns[self::MATING_TYPE_COLUMN]) );
                if (! empty($columns[self::TEMPERATURE_COLUMN])) {
                    $row->setTemperature( trim($columns[self::TEMPERATURE_COLUMN]) );
                } else {
                    $row->setTemperature( self::DEFAULT_TEMPERATURE );
                }
                $row->setPubmedId( trim($columns[self::PUBMED_ID_COLUMN]) );
                $rows[] = $row;
            }
        }
        fclose($file);
        return $rows;
    }
}
