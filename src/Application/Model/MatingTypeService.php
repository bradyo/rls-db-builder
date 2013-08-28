<?php

namespace Application\Model;

class MatingTypeService 
{
    public function getNormalizedMatingType($input) {
        $validAs = array('a', 'MATa');
        $validAlphas = array('α', 'alpha', 'MATalpha', 'MATα');
        $validDiploids = array('diploid', 'α/a', 'a/α', 'MATdiploid');
        $validOthers = array('sterile', '');

        $input = strtolower(trim($input));
        if (in_array($input, array_map('strtolower', $validAlphas))) {
            return 'MATalpha';
        } 
        elseif (in_array($input, array_map('strtolower', $validAs))) {
            return 'MATa';
        } 
        elseif (in_array($input, array_map('strtolower', $validDiploids))) {
            return 'diploid';
        } 
        elseif (in_array($input, array_map('strtolower', $validOthers))) {
            return $input;
        } 
        else {
            return null;
        }
    }
}
