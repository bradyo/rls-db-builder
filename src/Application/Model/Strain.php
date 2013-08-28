<?php

namespace Application\Model;

class Strain 
{
    private $id;
    private $name;
    private $background;
    private $matingType;
    private $genotype;
    private $shortGenotype;
    private $poolingGenotype;
    private $comment;
    private $contactEmail;
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getBackground() {
        return $this->background;
    }

    public function setBackground($background) {
        $this->background = $background;
    }

    public function getMatingType() {
        return $this->matingType;
    }

    public function setMatingType($matingType) {
        $this->matingType = $matingType;
    }

    public function getGenotype() {
        return $this->genotype;
    }

    public function setGenotype($genotype) {
        $this->genotype = $genotype;
    }

    public function getShortGenotype() {
        return $this->shortGenotype;
    }

    public function setShortGenotype($shortGenotype) {
        $this->shortGenotype = $shortGenotype;
    }

    public function getPoolingGenotype() {
        return $this->poolingGenotype;
    }

    public function setPoolingGenotype($poolingGenotype) {
        $this->poolingGenotype = $poolingGenotype;
    }

    public function getComment() {
        return $this->comment;
    }

    public function setComment($comment) {
        $this->comment = $comment;
    }

    public function getContactEmail() {
        return $this->contactEmail;
    }

    public function setContactEmail($contactEmail) {
        $this->contactEmail = $contactEmail;
    }
}
