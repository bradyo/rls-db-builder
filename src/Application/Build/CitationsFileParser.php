<?php

namespace Application\Build;

use Application\Model\Citation;

class CitationsFileParser
{
    const PUBMED_ID_COLUMN = 0;
    const TITLE_COLUMN = 1;
    const FIRST_AUTHOR_COLUMN = 2;
    const YEAR_COLUMN = 3;
    const SUMMARY_COLUMN = 4;

    private $citations;
    
    /**
     * @param string $inputPath Path to input citations csv file
     */
    public function parse($inputPath) {
        // loop over rows in input file and import if not filtered out
        $file = fopen($inputPath, 'r');
        $headers = fgetcsv($file);
        
        $this->citations = array();
        while (($rowData = fgetcsv($file)) !== false) {
            $citation = new Citation();
            $citation->setPubmedId($rowData[self::PUBMED_ID_COLUMN]);
            $citation->setTitle($rowData[self::TITLE_COLUMN]);
            $citation->setAuthor($rowData[self::FIRST_AUTHOR_COLUMN]);
            $citation->setYear($rowData[self::YEAR_COLUMN]);
            $citation->setSummary($rowData[self::SUMMARY_COLUMN]);
            $this->citations[] = $citation;
        }
        fclose($file);
    }
    
    public function getCitations() {
        return $this->citations;
    }
}
