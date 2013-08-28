<?php
namespace Application\Build;

class ExperimentFileRow 
{
    public $id;
    public $referenceIds = array();
    public $label;
    public $strainName;
    public $media;
    public $temperature;
    public $lifespans = array();
    public $endStates = array();
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getReferenceIds() {
        return $this->referenceIds;
    }

    public function setReferenceIds($referenceIds) {
        $this->referenceIds = $referenceIds;
    }

    public function getLabel() {
        return $this->label;
    }

    public function setLabel($label) {
        $this->label = $label;
    }

    public function getStrainName() {
        return $this->strainName;
    }

    public function setStrainName($strainName) {
        $this->strainName = $strainName;
    }

    public function getMedia() {
        return $this->media;
    }

    public function setMedia($media) {
        $this->media = $media;
    }

    public function getTemperature() {
        return $this->temperature;
    }

    public function setTemperature($temperature) {
        $this->temperature = $temperature;
    }

    /**
     * @return int[] 
     */
    public function getLifespans() {
        return $this->lifespans;
    }

    /**
     * @param int[] $lifespans 
     */
    public function setLifespans($lifespans) {
        $this->lifespans = $lifespans;
    }

    /**
     * @return String[] 
     */
    public function getEndStates() {
        return $this->endStates;
    }

    /**
     * @param String[] $endStates 
     */
    public function setEndStates($endStates) {
        $this->endStates = $endStates;
    }
}
