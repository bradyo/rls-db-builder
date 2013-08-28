<?php

namespace Application\Utility;

class Math
{
    public static function getMean($values) {
        if (count($values) <= 0) {
            return null;
        }
        $mean =  array_sum($values) / count($values);
        return $mean;
    }
    
    public static function getMedian($values) {
        sort($values);
        $n = count($values);
        $middleIndex = floor(($n - 1) / 2);
        if ($n % 2) { 
            $median = $values[$middleIndex];
        } else {
            $leftValue = $values[$middleIndex];
            $rightValue = $values[$middleIndex + 1];
            $median = (($leftValue + $rightValue) / 2);
        }
        return $median;
    }
    
    public static function getSampleStandardDeviation($values, $mean = null) {
        $n = count($values);
        if ($n <= 1) {
            return 0;
        }
        if ($mean == null) {
            $mean = self::getMean($values);
        }
        $squaredSum = 0.0;
        foreach ($values as $value) {
            $squaredSum += pow($value - $mean, 2);
        }
        return (float) sqrt($squaredSum / ($n - 1));
    }
}
