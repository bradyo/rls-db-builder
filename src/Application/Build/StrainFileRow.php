<?php

namespace Application\Build;

use Application\Model\Sample;

class StrainFileRow
{
    private $name;
    private $contactEmail;
    private $background;
    private $matingType;
    private $genotype;
    private $shortGenotype;
    private $comment;

    public function getName() {
        return $this->name;
    }

    public function setName($name) {
        $this->name = $name;
    }

    public function getContactEmail() {
        return $this->contactEmail;
    }

    public function setContactEmail($contactEmail) {
        $this->contactEmail = $contactEmail;
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

    public function getComment() {
        return $this->comment;
    }

    public function setComment($comment) {
        $this->comment = $comment;
    }
}
