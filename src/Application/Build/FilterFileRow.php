<?php

namespace Application\Build;

class FilterFileRow
{
    const DEFAULT_MEDIA = 'YPD';

    private $background;
    private $matingType;
    private $shortGenotype;
    private $media;
    private $temperature;
    private $pubmedId;

    public function __construct() {
        $this->media = self::DEFAULT_MEDIA;
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

    public function getShortGenotype() {
        return $this->shortGenotype;
    }

    public function setShortGenotype($shortGenotype) {
        $this->shortGenotype = $shortGenotype;
    }

    public function getMedia() {
        return $this->media;
    }

    public function setMedia($media) {
        if (empty($media)) {
            $this->media = self::DEFAULT_MEDIA;
        } else {
            $this->media = $media;
        }
    }

    public function getTemperature() {
        return $this->temperature;
    }

    public function setTemperature($temperature) {
        $this->temperature = $temperature;
    }

    public function getPubmedId() {
        return $this->pubmedId;
    }

    public function setPubmedId($pubmedId) {
        $this->pubmedId = $pubmedId;
    }
}
