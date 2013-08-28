<?php
namespace Application\Build;

use Application\Model\GeneService;
use Application\Model\MatingTypeService;
use Application\Model\TemperatureService;
use Application\Model\Strain;
use \PDO;

class Filter
{
    private $genotypeService;
    private $matingTypeService;

    private $allowedStrainKeys;
    private $allowedSampleKeys;
    private $pubmedIdsBySampleKey;

    /**
     * @param PDO $db
     */
    public function __construct($db) {
        $this->genotypeService = new GeneService($db);
        $this->matingTypeService = new MatingTypeService();
        $this->temperatureService = new TemperatureService();

        $this->allowedStrainKeys = array();
        $this->allowedSampleKeys = array();
        $this->pubmedIdsBySampleKey = array();
    }

    /**
     * @param string $filePath Path to filters csv file
     */
    public function loadFile($filePath) {
        // parse rows out of data file
        $parser = new FilterFileParser();
        $rows = $parser->parseRows($filePath);

        // generate a filer item for each row in the file
        foreach ($rows as $row) {
            /* @var $row FilterFileRow */

            // normalize values in file before adding
            $normalizedGenotype = $this->genotypeService
                    ->getNormalizedGenotype($row->getShortGenotype());
            $normalizedMatingType = $this->matingTypeService
                    ->getNormalizedMatingType($row->getMatingType());
            $normalizedTemperature = $this->temperatureService
                    ->getNormalizedTemperature($row->getTemperature());

            // create index map for allowed strain keys
            $strainKey = $this->getStrainKey(
                $row->getBackground(),
                $normalizedMatingType,
                $normalizedGenotype
            );
            $this->allowedStrainKeys[$strainKey] = $strainKey;

            // create index map for allowed sample keys
            $sampleKey = $this->getSampleKey(
                $row->getBackground(),
                $normalizedMatingType,
                $normalizedGenotype,
                $row->getMedia(),
                $normalizedTemperature
            );
            $this->allowedSampleKeys[$sampleKey] = $sampleKey;

            // create index map of pubmed ids by sample key
            if (! isset($this->pubmedIdsBySampleKey[$sampleKey])) {
                $this->pubmedIdsBySampleKey[$sampleKey] = array();
            }
            $this->pubmedIdsBySampleKey[$sampleKey][] = $row->getPubmedId();
        }
    }

    /**
     * @param string $background
     * @param string $matingType
     * @param string $genotype
     * @return string unique key string for strain
     */
    public function getStrainKey($background, $matingType, $genotype) {
        return join('/', array(
            $background,
            $matingType,
            $genotype,
        ));
    }

    /**
     * @param string $strainKey
     * @return boolean
     */
    public function isStrainAllowed($strainKey) {
        return (isset($this->allowedStrainKeys[$strainKey]));
    }

    /**
     * @param string $background
     * @param string $matingType
     * @param string $genotype
     * @param string $media
     * @param string $temperature
     * @return string unique key string for sample
     */
    public function getSampleKey($background, $matingType, $genotype, $media, $temperature) {
        return join('/', array(
            $background,
            $matingType,
            $genotype,
            $media,
            $temperature
        ));
    }

    /**
     * @param string $sampleKey
     * @return boolean
     */
    public function isSampleAllowed($sampleKey) {
        return isset($this->allowedSampleKeys[$sampleKey]);
    }

    /**
     * @param string $sampleKey
     * @return int[] array of pubmed ids for the given sample key
     */
    public function getPubmedIds($sampleKey) {
        if (isset($this->pubmedIdsBySampleKey[$sampleKey])) {
            return $this->pubmedIdsBySampleKey[$sampleKey];
        } else {
            return array();
        }
    }
}
