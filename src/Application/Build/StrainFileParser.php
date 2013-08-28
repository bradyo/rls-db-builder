<?php

namespace Application\Build;

class StrainFileParser
{
    const NAME_COLUMN = 0;
    const CONTACT_EMAIL_COLUMN = 1;
    const BACKGROUND_COLUMN = 2;
    const MATING_TYPE_COLUMN = 3;
    const GENOTYPE_COLUMN = 4;
    const SHORT_GENOTYPE_COLUMN = 5;
    const COMMENT_COLUMN = 6;

    /**
     * @param string $filePath
     */
    public function parseRows($filePath) {
        // Open the file for reading, ensure auto_detect_line_endings is set
        ini_set('auto_detect_line_endings', true);
        $file = fopen($filePath, 'r');
        fgetcsv($file); // discard headers

        // loop over rows in input file and import if not filtered out
        $rows = array();
        while (($rowData = fgetcsv($file)) !== false) {
            if (count($rowData) >= 6) {
                $row = new StrainFileRow();
                $row->setName( trim($rowData[self::NAME_COLUMN]) );
                $row->setContactEmail( trim($rowData[self::CONTACT_EMAIL_COLUMN]) );
                $row->setBackground( trim($rowData[self::BACKGROUND_COLUMN]) );
                $row->setMatingType( trim($rowData[self::MATING_TYPE_COLUMN]) );
                $row->setGenotype( trim($rowData[self::GENOTYPE_COLUMN]) );
                $row->setShortGenotype( trim($rowData[self::SHORT_GENOTYPE_COLUMN]) );
                $rows[] = $row;
            }
        }
        fclose($file);
        return $rows;
    }
}
