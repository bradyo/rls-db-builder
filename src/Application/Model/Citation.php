<?php

namespace Application\Model;

class Citation
{
    private $id;
    private $pubmedId;
    private $title;
    private $author;
    private $year;
    private $summary;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getPubmedId() {
        return $this->pubmedId;
    }

    public function setPubmedId($value) {
        $this->pubmedId = $value;
    }

    public function getTitle() {
        return $this->title;
    }

    public function setTitle($value) {
        $this->title = $value;
    }

    public function getAuthor() {
        return $this->author;
    }

    public function setAuthor($value) {
        $this->author = $value;
    }

    public function getYear() {
        return $this->year;
    }

    public function setYear($value) {
        $this->year = $value;
    }

    public function getSummary() {
        return $this->summary;
    }

    public function setSummary($value) {
        $this->summary = $value;
    }

}
