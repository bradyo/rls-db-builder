<?php

namespace Application\Model;


class Comparison
{
    private $id;
    private $testSample;
    private $referenceSample;
    private $percentChange;
    private $ranksumU;
    private $ranksumP;
    
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
     * @return Sample 
     */
    public function getTestSample() {
        return $this->testSample;
    }

    /**
     * @param Sample $testSample
     */
    public function setTestSample($sample) {
        $this->testSample = $sample;
    }

    /**
     * @return Sample
     */
    public function getReferenceSample() {
        return $this->referenceSample;
    }

    /**
     * @param Sample $sample 
     */
    public function setReferenceSample($sample) {
        $this->referenceSample = $sample;
    }

    /**
     * @return double
     */
    public function getPercentChange() {
        return $this->percentChange;
    }

    /**
     * @param double $percentChange 
     */
    public function setPercentChange($percentChange) {
        $this->percentChange = $percentChange;
    }

    /**
     * @return double
     */
    public function getRanksumU() {
        return $this->ranksumU;
    }
    
    /**
     * @param double $ranksumU 
     */
    public function setRanksumU($ranksumU) {
        $this->ranksumU = $ranksumU;
    }

    /**
     * @return double
     */
    public function getRanksumP() {
        return $this->ranksumP;
    }

    /**
     * @param double $ranksumP
     */
    public function setRanksumP($ranksumP) {
        $this->ranksumP = $ranksumP;
    }
}

