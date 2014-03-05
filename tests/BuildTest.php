<?php
namespace Application\Test;

use PHPUnit_Framework_TestCase;
use Application\Build\Builder;
use Application\Model\StrainService;
use Application\Model\SampleService;
use Application\Model\ComparisonService;
use PDO;

class BuildTest extends PHPUnit_Framework_TestCase
{
    private $config;
    private $pdo;

    public function setUp() {
        parent::setUp();
        $config = require_once(__DIR__ . '/config.php');
        $this->config = $config;

        $database = $config['database'];
        $dsn = 'mysql:host=' . $database['host'] . ';dbname=' . $database['name'];
        $pdo = new \PDO($dsn, $database['user'], $database['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo = $pdo;
    }

    public function testBuildProcess() {
        echo "building\n";
        $builder = new Builder($this->config);
        $builder->build();

        echo "checking database\n";
        $this->checkStrainFiltering();
        $this->checkSampleFiltering();
        $this->checkSampleFilePooling();
        $this->checkComparisonFilePooling();
        $this->checkComparisonStrainPooling();
        $this->checkComparisonGenotypePooling();

        $this->checkMatingTypeCrossReference();
        $this->checkMediaCrossReference();

        echo "checking reports\n";
        // todo
    }

    private function checkStrainFiltering() {
        $strainService = new StrainService($this->pdo);

        // test that we have all represented strains
        $strain = $strainService->findOneByName('WildType1');

        $this->assertNotEquals(null, $strain);
        $this->assertEquals('BY4742', $strain->getBackground());
        $this->assertEquals('MATalpha', $strain->getMatingType());
        $this->assertEquals('BY4742', $strain->getShortGenotype());

        $strain = $strainService->findOneByName('Strain1');
        $this->assertNotEquals(null, $strain);
        $this->assertEquals($strain->getBackground(), 'BY4742');
        $this->assertEquals($strain->getMatingType(), 'MATalpha');
        $this->assertEquals($strain->getShortGenotype(), 'gene1');

        $strain = $strainService->findOneByName('Strain2');
        $this->assertNotEquals(null, $strain);
        $this->assertEquals('BY4742', $strain->getBackground());
        $this->assertEquals('MATalpha', $strain->getMatingType());
        $this->assertEquals('gene1', $strain->getShortGenotype());

        $strain = $strainService->findOneByName('Strain3');
        $this->assertNotEquals(null, $strain);
        $this->assertEquals('BY4742', $strain->getBackground());
        $this->assertEquals('MATalpha', $strain->getMatingType());
        $this->assertEquals('gene2', $strain->getShortGenotype());

        // test that Strain4 was filtered out of strains (not in filter file)
        $strain = $strainService->findOneByName('Strain4');
        $this->assertEquals(null, $strain);
    }

    private function checkSampleFiltering() {
        $sampleService = new SampleService($this->pdo);

        // check that the right number of samples were accepted
        $samples = $sampleService->fetchAll(array(
            'pooled_by' => 'file'
        ));
        $this->assertEquals(8, count($samples));

        // check strain with temperature not in list doesn't exist
        $samples = $sampleService->fetchAll(array(
            'label' => 'Strain1 Media1 18C',
        ));
        $this->assertEquals(0, count($samples));

        // check strain with media not in list doesn't exist
        $samples = $sampleService->fetchAll(array(
            'label' => 'Strain1 Media2',
        ));
        $this->assertEquals(0, count($samples));

        // check that strain not in list doesn't exist
        $samples = $sampleService->fetchAll(array(
            'label' => 'Strain4 Media1',
        ));
        $this->assertEquals(0, count($samples));
    }

    private function checkSampleFilePooling() {
        // fetch a strain with an omitted lifespan (0 days)
        $sampleService = new SampleService($this->pdo);
        $sample = $sampleService->fetchOne(array(
            'pooled_by' => 'file',
            'experiment_name' => '1.csv',
            'label' => 'Strain1 Media1',
        ));

        // check lifespans are correct
        $this->assertEquals('18,19,22', $sample['lifespans']); // "0" omitted
        $this->assertEquals(3, $sample['lifespans_count']);
        $this->assertEquals(1, $sample['lifespans_omitted_count']);

        // check calculations
        $this->assertEquals(19.666667, $sample['lifespans_mean'], '', 0.01);
        $this->assertEquals(19, $sample['lifespans_median']);
        $this->assertEquals(2.081665, $sample['lifespans_stdev'], '', 0.01);

        // fetch sample data that has pooled lifespans
        $sampleService = new SampleService($this->pdo);
        $sample = $sampleService->fetchOne(array(
            'pooled_by' => 'file',
            'experiment_name' => '1.csv',
            'label' => 'WildType1 Media1',
        ));
        $this->assertEquals($sample['lifespans'], '10,11,12,13,14,15');
    }

    private function checkComparisonFilePooling() {
        // check that the comparison calculations are done correctly
        $comparisonService = new ComparisonService($this->pdo);
        $comparisons = $comparisonService->fetchAll(array(
            'pooled_by' => 'file',
            'test_sample_label' => 'WildType1 Media1',
            'test_sample_media' => 'Media1',
            'test_sample_temperature' => '30.000',
        ));
        $this->assertEquals(count($comparisons), 1);
        $comparison = $comparisons[0];

        // test pooled lifespans are correct
        $this->assertEquals($comparison['test_sample_lifespans'], '18,19,20');
        $this->assertEquals($comparison['reference_sample_lifespans'], '10,11,12,13,14,15');

        // test calculations are correct
        $this->assertEquals($comparison['percent_change'], -28.0, '', 0.001);
        $this->assertEquals($comparison['ranksum_p'], 0.03571, '', 0.001);
    }

    private function checkComparisonStrainPooling() {
        // test official file pooled comparisons
        $comparisonService = new ComparisonService($this->pdo);
        $comparisons = $comparisonService->fetchAll(array(
            'pooled_by' => 'strain',
            'test_sample_strain_name' => 'Strain1',
            'test_sample_media' => 'Media1',
            'test_sample_temperature' => '30',
        ));
        $this->assertEquals(count($comparisons), 1);
        $comparison = $comparisons[0];

        // check test lifespans
        $lifespans = $comparison['testLifespans'];
        $normalizedLifespans = sort(explode(',', $lifespans), SORT_NUMERIC);
        $lifespanString = join(',', $normalizedLifespans);
        $this->assertEquals($lifespanString, '18,19,20,21,22,23');

        // check reference lifespans
        $lifespans = $comparison['referenceLifespans'];
        $normalizedLifespans = sort(explode(',', $lifespans), SORT_NUMERIC);
        $lifespanString = join(',', $normalizedLifespans);
        $this->assertEquals($lifespanString, '7,8,9,10,11,12,13,14,15');
    }

    private function checkComparisonGenotypePooling() {
        // todo
    }

    private function checkMatingTypeCrossReference() {
        // todo
    }

    private function checkMediaCrossReference() {
        // todo
    }
}