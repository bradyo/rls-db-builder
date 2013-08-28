<?php
namespace Application\Build;

use Application\Debug;
use Application\Model\Experiment;
use Applicaiton\Model\Citation;
use Application\Model\Strain;
use Application\Model\Sample;
use Application\Model\Comparison;
use Application\Model\Cell;

use Application\Model\ExperimentService;
use Application\Model\StrainService;
use Application\Model\CitationService;
use Application\Model\CellService;
use Application\Model\SampleService;
use Application\Model\ComparisonService;
use Application\Model\TemperatureService;

use PDO;


class ExperimentImporter
{
    const POOLED_BY = 'file';

    private $filter;
    private $experimentService;
    private $citationService;
    private $strainService;
    private $cellService;
    private $sampleService;
    private $comparisonService;
    private $temperatureService;

    private $comparisons;

    /**
     * @param PDO $db
     */
    public function __construct($db) {
        $this->experimentService = new ExperimentService($db);
        $this->citationService = new CitationService($db);
        $this->strainService = new StrainService($db);
        $this->cellService = new CellService($db);
        $this->sampleService = new SampleService($db);
        $this->comparisonService = new ComparisonService($db);
        $this->temperatureService = new TemperatureService();

        $this->comparisons = array();
    }

    /**
     * @param Filter $filter
     */
    public function setFilter($filter) {
        $this->filter = $filter;
    }

    /**
     * @param string $experimentFilePath
     */
    public function import($experimentFilePath) {
        // save experiment
        $experiment = new Experiment();
        $experiment->setFilename(basename($experimentFilePath));
        $this->experimentService->saveExperiment($experiment);

        // get combined rows from experiment file
        $parser = new ExperimentFileParser();
        $parser->parse($experimentFilePath);

        $combiner = new ExperimentFileCombiner();
        $combinedRows = $combiner->combine($parser->getRows());

        // convert combined rows into samples and save
        $samplesById = array();
        $samplesByRowId = array();
        foreach ($combinedRows as $combinedRow) {
            /* @var $combinedRow ExperimentFileRow */

            // get set strain data
            $strainName = $combinedRow->getStrainName();
            $strain = $this->strainService->findOneByName($strainName);
            if ($strain === null) {
                continue; // skip
            }

            $normalizedTemperature = $this->temperatureService
                    ->getNormalizedTemperature($combinedRow->getTemperature());

            // check sample against filter and get citations
            $citations = array();
            if ($this->filter !== null) {
                // check data against filter
                $sampleKey = $this->filter->getSampleKey(
                    $strain->getBackground(),
                    $strain->getMatingType(),
                    $strain->getPoolingGenotype(),
                    $combinedRow->getMedia(),
                    $normalizedTemperature
                );

                echo "sample key: ", $sampleKey;
                if (! $this->filter->isSampleAllowed($sampleKey)) {
                    echo " => not allowed\n";
                    continue; // skip
                }

                // fetch citations for this sample
                $pubmedIds = $this->filter->getPubmedIds($sampleKey);
                $citations = $this->citationService->findAll(array('pubmedIds' => $pubmedIds));
            }

            // create cells
            $cells = array();
            $lifespans = $combinedRow->getLifespans();
            $endStates = $combinedRow->getEndStates();
            $cellsCount = count($combinedRow->getLifespans());
            for ($i = 0; $i < $cellsCount; $i++) {
                $cell = new Cell();
                $cell->setExperiment($experiment);
                $cell->setLabel($combinedRow->getLabel());
                $cell->setStrain($strain);
                $cell->setMedia($combinedRow->getMedia());
                $cell->setTemperature($normalizedTemperature);
                $cell->setLifespan($lifespans[$i]);
                $cell->setEndState($endStates[$i]);
                $cell->setCitations($citations);

                $this->cellService->save($cell);
                $cells[] = $cell;
            }

            // save sample data
            $sample = new Sample();
            $sample->setPooledBy(self::POOLED_BY);
            $sample->setLabel($combinedRow->getLabel());
            $sample->setStrainName($combinedRow->getStrainName());
            if ($strain !== null) {
                $sample->setBackground($strain->getBackground());
                $sample->setMatingType($strain->getMatingType());
                $sample->setGenotype($strain->getPoolingGenotype());
            }
            $sample->setMedia($combinedRow->getMedia());
            $sample->setTemperature($normalizedTemperature);
            $sample->setCells($cells);

            $this->sampleService->save($sample);

            // index samples by id for comparison mapping
            $sampleId = $sample->getId();
            $samplesById[$sampleId] = $sample;

            // index by combined row id so we can map references
            $rowId = $combinedRow->getId();
            $samplesByRowId[$rowId] = $sample;
        }

        // map references defined in file
        $referenceIdsBySampleId = array();
        foreach ($combinedRows as $combinedRow) {
            /* @var $combinedRow ExperimentFileRow */
            $referenceIds = array();
            foreach ($combinedRow->getReferenceIds() as $referenceRowId) {
                if (isset($samplesByRowId[$referenceRowId])) {
                    $referenceSample = $samplesByRowId[$referenceRowId];
                    $referenceIds[] = $referenceSample->getId();
                }
            }
            if (isset($samplesByRowId[$combinedRow->getId()])) {
                $sample = $samplesByRowId[$combinedRow->getId()];
                $sampleId = $sample->getId();
                $referenceIdsBySampleId[$sampleId] = $referenceIds;
            }
        }

        // add computed standard references
        $referenceMapper = new ReferenceMapper();
        $referenceMapper->map($samplesById);
        $standardReferenceIds = $referenceMapper->getReferenceIdsBySampleId();
        foreach ($standardReferenceIds as $sampleId => $referenceIds) {
            $existingReferenceIds = $referenceIdsBySampleId[$sampleId];
            $combinedReferenceIds = array();
            foreach ($referenceIds as $referenceId) {
                if (! in_array($referenceId, $existingReferenceIds)) {
                    $combinedReferenceIds[] = $referenceId;
                }
            }
            $referenceIdsBySampleId[$sampleId] = $combinedReferenceIds;
        }

        // loop over reference mapping and save a comparisons for each entry
        foreach ($referenceIdsBySampleId as $sampleId => $referenceIds) {
            $sample = $samplesById[$sampleId];
            foreach ($referenceIds as $referenceId) {
                $referenceSample = $samplesById[$referenceId];

                $comparison = new Comparison();
                $comparison->setTestSample($sample);
                $comparison->setReferenceSample($referenceSample);

                $this->comparisonService->save($comparison);
                $this->comparisons[] = $comparison;
            }
        }
    }

    public function getComparisons() {
        return $this->comparisons;
    }
}
