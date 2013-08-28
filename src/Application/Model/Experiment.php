<?php

namespace Application\Model;

/**
 * @property integer $id
 * @property string $namespace
 * @property string $filename
 * @property string $name
 * @property string $contactEmail 
 */
class Experiment 
{
    private $id;
    private $namespace;
    private $filename;
    private $name;
    private $contactEmail;

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function getNamespace() {
        return $this->namespace;
    }
    
    public function getFilename() {
        return $this->filename;
    }

    public function setFilename($filename) {
        $this->filename = $filename;
    }

    public function setNamespace($namespace) {
        $this->namespace = $namespace;
    }

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

}
