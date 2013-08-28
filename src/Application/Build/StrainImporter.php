<?php
namespace Application\Build;

use Application\Model\GeneService;
use Application\Model\StrainService;
use Application\Model\MatingTypeService;
use Application\Model\Strain;
use \PDO;

class StrainImporter
{
    private $strainService;
    private $geneService;
    private $matingTypeService;
    private $filter;

    /**
     * @param PDO $db
     */
    public function __construct($db) {
        $this->strainService = new StrainService($db);
        $this->geneService = new GeneService($db);
        $this->matingTypeService = new MatingTypeService();
    }

    /**
     * @param Filter $filter
     */
    public function setFilter($filter) {
        $this->filter = $filter;
    }

    /**
     * Applies filter to input file and inports into database
     *
     * @param string $filePath Path to input strains csv file
     */
    public function import($filePath) {
        $parser = new StrainFileParser();
        $rows = $parser->parseRows($filePath);
        foreach ($rows as $row) {
            /* @var $row StrainFileRow */
            $matingType = $this->matingTypeService->getNormalizedMatingType($row->getMatingType());
            $poolingGenotype = $this->geneService->getNormalizedGenotype($row->getShortGenotype());

            $strain = new Strain();
            $strain->setName($row->getName());
            $strain->setContactEmail($row->getContactEmail());
            $strain->setBackground($row->getBackground());
            $strain->setMatingType($matingType);
            $strain->setGenotype($row->getGenotype());
            $strain->setShortGenotype($row->getShortGenotype());
            $strain->setPoolingGenotype($poolingGenotype);
            $strain->setComment($row->getComment());

            // check if strain should be added
            if ($this->filter !== null) {
                $strainKey = $this->filter->getStrainKey(
                    $strain->getBackground(),
                    $strain->getMatingType(),
                    $strain->getPoolingGenotype()
                );
                if (! $this->filter->isStrainAllowed($strainKey)) {
                    continue; // skip
                }
            }
            $this->strainService->save($strain);
        }
    }
}
