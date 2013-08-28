<?php
namespace Application\Build\ComparisonPooling;

use Application\Model\Sample;
use Application\Model\Comparison;

class ComparisonPooler
{
    /**
     * @param Comparison[] $comparison
     * @return Comparison[] pooled comparisons
     */
    public function getPooledComparisons($comparisons) {
        $pooledComparisonsByKey = array();
        foreach ($comparisons as $comparison) {
            // hook for skipping comparisons
            if (! $this->needsPooling($comparison)) {
                continue;
            }

            // get corresponding pooled comparison for this comparison
            $pooledComparison = null;
            $poolingKey = $this->getPoolingKey($comparison);
            if (! isset($pooledComparisonsByKey[$poolingKey])) {
                $pooledComparison = $this->createPooledComparison($comparison);
                $pooledComparisonsByKey[$poolingKey] = $pooledComparison;
            } else {
                $pooledComparison = $pooledComparisonsByKey[$poolingKey];
            }

            // merge sample cells
            $pooledComparison->getTestSample()->addCells(
                $comparison->getTestSample()->getCells()
            );
            $pooledComparison->getReferenceSample()->addCells(
                $comparison->getReferenceSample()->getCells()
            );
        }
        return array_values($pooledComparisonsByKey);
    }

    /**
     * @param Comparison $comparison
     * @return boolean
     */
    protected function needsPooling($comparison) {
        return true;
    }

    /**
     * @param Comparison $comparison
     * @return Comparison new pooled comparison object
     */
    protected function createPooledComparison($comparison) {
        $pooledComparison = new Comparison();
        $pooledComparison->setTestSample(
            $this->createPooledSample($comparison->getTestSample())
        );
        $pooledComparison->setReferenceSample(
            $this->createPooledSample($comparison->getReferenceSample())
        );
        return $pooledComparison;
    }

    /**
     * @param Sample $sample
     * @return Sample
     */
    protected function createPooledSample($sample) {
        $pooledSample = new Sample();
        $pooledSample->setPooledBy($sample->getPooledBy());
        $pooledSample->setLabel($sample->getGenotype());
        $pooledSample->setBackground($sample->getBackground());
        $pooledSample->setMatingType($sample->getMatingType());
        $pooledSample->setGenotype($sample->getGenotype());
        $pooledSample->setMedia($sample->getMedia());
        $pooledSample->setTemperature($sample->getTemperature());
        return $pooledSample;
    }

    /**
     * @param Comparison $comparison
     * @return string unique pooling key for a comparison
     */
    protected function getPoolingKey($comparison) {
        return join('/', array(
            $this->getSamplePoolingKey($comparison->getTestSample()),
            $this->getSamplePoolingKey($comparison->getReferenceSample()),
        ));
    }

    /**
     * @param Sample $sample
     * @return string unique pooling key for a sample
     */
    protected function getSamplePoolingKey($sample) {
        return join('/', array(
            $sample->getBackground(),
            $sample->getMatingType(),
            $sample->getGenotype(),
            $sample->getMedia(),
            $sample->getTemperature()
        ));
    }
}
