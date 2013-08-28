<?php

namespace Application\Build;

use Application\Model\CitationService;

class CitationsImporter
{
    private $citationService;

    /**
     * @param CitationService $citationService
     */
    public function __construct($citationService) {
        $this->citationService = $citationService;
    }

    /**
     * @param string $inputPath Path to input citations csv file
     */
    public function import($inputPath) {
        $citationsParser = new CitationsFileParser();
        $citationsParser->parse($inputPath);
        $citations = $citationsParser->getCitations();
        foreach ($citations as $citation) {
            $this->citationService->save($citation);
        }
    }
}
