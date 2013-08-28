<?php
namespace Application\Build\ComparisonPooling;

use Application\Model\Sample;

class GenotypePooler extends ComparisonPooler
{
    const POOLED_BY = 'genotype';

    /**
     * @param Sample $sample
     * @return Sample
     */
    protected function createPooledSample($sample) {
        $pooledSample = parent::createPooledSample($sample);
        $pooledSample->setPooledBy(self::POOLED_BY);
        $pooledSample->setLabel($sample->getGenotype());
        return $pooledSample;
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
