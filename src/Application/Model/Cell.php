<?php

namespace Application\Model;

class Cell 
{
    private $id;
    private $experiment;
    private $label;
    private $strain;
    private $media;
    private $temperature;
    private $lifespan;
    private $endState;
    private $divisions;
    private $citations;
    
    public function __construct() {
        $this->divisions = array();
        $this->citations = array();
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
     * @return Experiment 
     */
    public function getExperiment() {
        return $this->experiment;
    }

    /**
     * @param Experiment $experiment 
     */
    public function setExperiment($experiment) {
        $this->experiment = $experiment;
    }

    public function getLabel() {
        return $this->label;
    }

    public function setLabel($label) {
        $this->label = $label;
    }

    /**
     * @return Strain
     */
    public function getStrain() {
        return $this->strain;
    }

    /**
     * @param Strain $strain 
     */
    public function setStrain($strain) {
        $this->strain = $strain;
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

    public function getLifespan() {
        return $this->lifespan;
    }

    public function setLifespan($lifespan) {
        $this->lifespan = $lifespan;
    }

    public function getEndState() {
        return $this->endState;
    }

    public function setEndState($endState) {
        $this->endState = $endState;
    }
    
    /**
     * @return int[]
     */
    public function getDivisions() {
        return $this->divisions;
    }

    /**
     * @param int[] $divisions 
     */
    public function setDivisions($divisions) {
        $this->divisions = $divisions;
    }

    /**
     * @return Citation[] 
     */
    public function getCitations() {
        return $this->citations;
    }

    /**
     * @param Citation[] $citations 
     */
    public function setCitations($citations) {
        $this->citations = $citations;
    }

}
