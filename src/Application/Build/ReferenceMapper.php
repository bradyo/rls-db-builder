<?php
namespace Application\Build;

use Application\Model\Sample;

class ReferenceMapper
{
    private $standardMedias;
    private $standardGenotypes;
    private $referenceIds;

    public function __construct() {
        $this->standardMedias = array(
            'YPD',
        );
        $this->standardGenotypes = array(
            'BY4741',
            'BY4742',
            'BY4743',
            'W303AR',
            'PSY316AUT',
            'PSY316AT',
        );
        $this->referenceIds = array();
    }

    /**
     * @param Sample[] $samples
     */
    public function map($samples) {
        // Compare each sample to every other sample, checking if other sample
        // should be added as a reference to the target sample.
        foreach ($samples as $targetSample) {
            // Get target medias to check for
            $targetMedias = $this->standardMedias;

            // Get target genotypes to check for, including standard wild type
            // genotypes and all combinations of sub-genotypes (i.e. "tor1" and
            // "gcn4" for "tor1 gcn4").
            $targetGenotypes = array_merge(
                $this->standardGenotypes,
                $this->getSubGenotypeCombinations($targetSample)
                );

            // check all other samples to see if they should be references of
            // target sample
            foreach ($samples as $otherSample) {
                $isMediaMatch = $this->isMediaMatch($targetSample, $otherSample,
                        $targetMedias);
                $isGenotypeMatch = $this->isGenotypeMatch($targetSample, $otherSample,
                        $targetGenotypes);
                if ($isMediaMatch || $isGenotypeMatch) {
                    $this->addReference($targetSample->getId(), $otherSample->getId());
                }
            }
        }
    }

    /**
     * @return array array of reference ids indexed by sample id
     */
    public function getReferenceIdsBySampleId() {
        return $this->referenceIds;
    }

    /**
     * @param Sample $sample
     * @param Sample $otherSample
     * @return boolean
     */
    private function isMediaMatch($sample, $otherSample, $targetMedias) {
        $isComparable = $sample->getId() !== $otherSample->getId()
            && $sample->getBackground() == $otherSample->getBackground()
            && $sample->getMatingType() == $otherSample->getMatingType()
            && $sample->getGenotype() == $otherSample->getGenotype()
            && $sample->getTemperature() == $otherSample->getTemperature()
            ;
        if ($isComparable && in_array($otherSample->getMedia(), $targetMedias)) {
            return true;
        }
        return false;
    }

    /**
     * @param Sample $sample
     * @param Sample $otherSample
     * @return boolean
     */
    private function isGenotypeMatch($sample, $otherSample, $targetGenotypes) {
        $isComparable = $sample->getId() !== $otherSample->getId()
            && $sample->getBackground() == $otherSample->getBackground()
            && $sample->getMatingType() == $otherSample->getMatingType()
            && $sample->getMedia() == $otherSample->getMedia()
            && $sample->getTemperature() == $otherSample->getTemperature()
            ;
        if ($isComparable) {
            foreach ($targetGenotypes as $targetGenotype) {
                if ($otherSample->getGenotype() == $targetGenotype) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param int $sampleId
     * @param int $referenceSampleId
     */
    private function addReference($sampleId, $referenceSampleId) {
        if (! isset($this->referenceIds[$sampleId])) {
            $this->referenceIds[$sampleId] = array();
        }
        if (! in_array($referenceSampleId, $this->referenceIds[$sampleId])) {
            $this->referenceIds[$sampleId][] = $referenceSampleId;
        }
    }

    /**
     * @param Sample $sample
     * @return string[]
     */
    private function getSubGenotypeCombinations($sample) {
        // add all unique combinations of sub genotypes (i.e. all combinations
        // of single mutants for a given double). The code below creates a
        // power set of all combinations of genotype units.
        $subGenotypes = array();
        $genotypeUnits = explode(' ', $sample->getGenotype());
        $powerSets = $this->getPowerSets($genotypeUnits);
        foreach ($powerSets as $setItems) {
            // make sure strings are sorted so they match normalized genotypes
            sort($setItems);

            $subGenotype = join(' ', $setItems);
            if ($subGenotype === $sample->getGenotype()) {
                continue; // skip if equal to full genotype
            }
            if (! in_array($subGenotype, $subGenotypes)) {
                $subGenotypes[] = $subGenotype;
            }
        }
        return $subGenotypes;
    }

    /**
     * @param array $items array of strings to combine in all possible combinations
     * @return array combinations of strings
     */
    private function getPowerSets($items) {
        $count = count($items);
        $members = pow(2, $count);
        $results = array();
        for ($i = 0; $i < $members; $i++) {
            $b = sprintf("%0".$count."b", $i);
            $out = array();
            for ($j = 0; $j < $count; $j++) {
                if ($b{$j} == '1') $out[] = $items[$j];
            }
            if (count($out) >= 1) {
                $results[] = $out;
            }
        }
        return $results;
    }
}

