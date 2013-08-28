<?php
namespace Application\Build\ComparisonPooling;

use Application\Model\Sample;
use Application\Model\Comparison;

class GenotypeMixedMatingPooler extends ComparisonPooler
{
    const POOLED_BY = 'genotype-MATa/MATalpha';
    const MATING_TYPE = "MATa/MATalpha";

    private $targetMatingTypes;

    public function __construct() {
        $this->targetMatingTypes = array(
            'MATa',
            'MATalpha'
        );
    }

    /**
     * @param Comparison $comparison
     */
    protected function needsPooling($comparison) {
        $matingType = $comparison->getTestSample()->getMatingType();
        $isTestSampleOk = in_array($matingType, $this->targetMatingTypes);

        $matingType = $comparison->getReferenceSample()->getMatingType();
        $isRefSampleOk = in_array($matingType, $this->targetMatingTypes);

        if ($isTestSampleOk && $isRefSampleOk) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param Sample $sample
     * @return Sample
     */
    protected function createPooledSample($sample) {
        $pooledSample = parent::createPooledSample($sample);
        $pooledSample->setPooledBy(self::POOLED_BY);
        $pooledSample->setLabel($sample->getGenotype());
        $pooledSample->setMatingType(self::MATING_TYPE);
        return $pooledSample;
    }

    /**
     * @param Sample $sample
     * @return string unique pooling key for a sample
     */
    protected function getSamplePoolingKey($sample) {
        return join('/', array(
            $sample->getBackground(),
            $sample->getGenotype(),
            $sample->getMedia(),
            $sample->getTemperature()
        ));
    }
}
