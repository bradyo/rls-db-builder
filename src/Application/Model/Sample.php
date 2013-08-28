<?php
namespace Application\Model;

use Application\Utility\Math;

class Sample
{
    private $id;
    private $pooledBy;
    private $label;
    private $strainName;
    private $background;
    private $matingType;
    private $genotype;
    private $media;
    private $temperature;
    private $cells;

    private $lifespans;
    private $lifespansMean;
    private $lifespansMedian;
    private $lifespansStdev;
    private $lifespansCount;
    private $lifespansOmittedCount;
    private $endStates;

    private $needsCellDerivedComputation;


    public function __construct() {
        $this->cells = new \SplObjectStorage();
        $this->lifespans = array();
        $this->endStates = array();
        $this->needsCellDerivedComputation = false;
    }

    /**
     * @return int
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getPooledBy() {
        return $this->pooledBy;
    }

    /**
     * @param string $value
     */
    public function setPooledBy($value) {
        $this->pooledBy = $value;
    }

    /**
     * @return string
     */
    public function getLabel() {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label) {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getStrainName() {
        return $this->strainName;
    }

    /**
     * @param string $strain
     */
    public function setStrainName($value) {
        $this->strainName = $value;
    }

    /**
     * @return string
     */
    public function getGenotype() {
        return $this->genotype;
    }

    /**
     * @param string $value
     */
    public function setGenotype($value) {
        $this->genotype = $value;
    }

    /**
     * @return string
     */
    public function getBackground() {
        return $this->background;
    }

    /**
     * @param string $value
     */
    public function setBackground($value) {
        $this->background = $value;
    }

    /**
     * @return string
     */
    public function getMatingType() {
        return $this->matingType;
    }

    /**
     * @param string $value
     */
    public function setMatingType($value) {
        $this->matingType = $value;
    }

    /**
     * @return string
     */
    public function getMedia() {
        return $this->media;
    }

    /**
     * @param string $value
     */
    public function setMedia($value) {
        $this->media = $value;
    }

    /**
     * @return string degrees in celcius to 3 decimal places
     */
    public function getTemperature() {
        return $this->temperature;
    }

    /**
     * @param string $value degrees in celcius to 3 decimal places
     */
    public function setTemperature($value) {
        $this->temperature = $value;
    }

    /**
     * @return Cell[]
     */
    public function getCells() {
        return $this->cells;
    }

    /**
     * @param Cell[] $cells
     */
    public function setCells($cells) {
        foreach ($cells as $cell) {
            $this->cells->attach($cell);
        }
        $this->needsCellDerivedComputation = true;
    }

    /**
     * @param Cell $cell
     */
    public function addCell($cell) {
        $this->cells->attach($cell);
        $this->needsCellDerivedComputation = true;
    }

    /**
     * @param Cell[] $cells
     */
    public function addCells($cells) {
        foreach ($cells as $cell) {
            $this->cells->attach($cell);
        }
        $this->needsCellDerivedComputation = true;
    }

    private function computeCellDerivedDataIfNeeded() {
        if ($this->needsCellDerivedComputation === true) {
            $this->computeCellDerivedData();
        }
    }

    private function computeCellDerivedData() {
        // calculate and save cell derived data
        $lifespans = array();
        $endStates = array();
        foreach ($this->cells as $cell) {
            $lifespan = $cell->getLifespan();
            if ($lifespan > 1) {
                $lifespans[] = $lifespan;
                $endStates[] = $cell->getEndState();
            }
        }
        sort($lifespans, SORT_NUMERIC);
        $this->lifespans = $lifespans;
        $this->lifespansMean = Math::getMean($lifespans);
        $this->lifespansMedian = Math::getMedian($lifespans);
        $this->lifespansStdev = Math::getSampleStandardDeviation($lifespans,
                $this->lifespansMean);
        $this->lifespansCount = count($lifespans);
        $this->lifespansOmittedCount = count($this->cells) - count($lifespans);
        $this->endStates = $endStates;

        // update status
        $this->needsCellDerivedComputation = false;
    }

    public function getLifespansMean() {
        $this->computeCellDerivedDataIfNeeded();
        return $this->lifespansMean;
    }

    public function getLifespansMedian() {
        $this->computeCellDerivedDataIfNeeded();
        return $this->lifespansMedian;
    }

    public function getLifespansStdev() {
        $this->computeCellDerivedDataIfNeeded();
        return $this->lifespansStdev;
    }

    public function getLifespans() {
        $this->computeCellDerivedDataIfNeeded();
        return $this->lifespans;
    }

    public function getLifespansCount() {
        $this->computeCellDerivedDataIfNeeded();
        return $this->lifespansCount;
    }

    public function getLifespansOmittedCount() {
        $this->computeCellDerivedDataIfNeeded();
        return $this->lifespansOmittedCount;
    }

    public function getEndStates() {
        $this->computeCellDerivedDataIfNeeded();
        return $this->endStates;
    }
}
