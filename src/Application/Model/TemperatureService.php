<?php

namespace Application\Model;

class TemperatureService
{
    public function getNormalizedTemperature($input) {
        if ($input === null) {
            return $input;
        } else {
            return sprintf('%.3d', $input);
        }
    }
}
