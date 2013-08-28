<?php
namespace Application\Build;

use Application\Build\ComparisonPooling\ComparisonPooler;
use Application\Build\ComparisonPooling\GenotypeMixedMatingPooler;
use Application\Build\ComparisonPooling\GenotypePooler;
use Application\Build\ComparisonPooling\StrainPooler;
use Application\Debug;
use Application\Model\CitationService;
use Application\Model\Comparison;
use Application\Model\ComparisonService;
use Application\Model\SampleService;
use PDO;

class Builder
{
    const FILTER_FILENAME = 'filters.csv';
    const STRAINS_FILENAME = 'strains.csv';
    const EXPERIMENTS_FOLDER = 'experiments';
    const CITATIONS_FILENAME = 'citations.csv';

    private $pdo;
    private $comparisonAnalyzer;
    private $inputPath;

    /**
     * @param array $config
     */
    public function __construct(array $config) {
        $this->inputPath = $config['input_path'];

        $database = $config['database'];
        $dsn = 'mysql:host=' . $database['host'] . ';dbname=' . $database['name'];
        $pdo = new \PDO($dsn, $database['user'], $database['password']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->pdo = $pdo;

        $this->comparisonAnalyzer = new ComparisonAnalyzer($config['database'], $config['r_exec_path']);
    }

    public function build() {
        // truncate target tables
        $targetTables = array(
            'build_meta',
            'cell_citation',
            'citation',
            'strain',
            'experiment',
            'sample_cell',
            'cell',
            'sample',
            'comparison',
            'across_media',
            'across_mating_type'
        );
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($targetTables as $table) {
            $this->pdo->exec('TRUNCATE ' . $table);
        }
        $this->pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

        // import global citations file
        $citationsFilePath = $this->getCitationsFilePath();
        if (is_file($citationsFilePath)) {
            $citationService = new CitationService($this->pdo);
            $citationImporter = new CitationsImporter($citationService);
            $citationImporter->import($citationsFilePath);
        } else {
            echo "warning: citations file does not exist at $citationsFilePath\n";
        }

        // load filter file if it exists
        $filter = null;
        $filterFilePath = $this->getFilterFilePath();
        if (is_file($filterFilePath)) {
            $filter = new Filter($this->pdo);
            $filter->loadFile($filterFilePath);
        } else {
            echo "warning: filter file not found at $filterFilePath\n";
        }

        // import strains
        $strainsFilePath = $this->getStrainsFilePath();
        if (is_file($strainsFilePath)) {
            // try importing strains in a transaction
            $strainsImporter = new StrainImporter($this->pdo);
            $strainsImporter->setFilter($filter);
            $strainsImporter->import($strainsFilePath);
        }

        // import experiments
        $comparisons = array();
        $folderPath = $this->getExperimentsFolderPath();
        $fileNames= $this->getExperimentFileNames($folderPath);
        foreach ($fileNames as $filename) {
            $filePath = $folderPath . '/' . $filename;

            // import experiment file
            $experimentImporter = new ExperimentImporter($this->pdo);
            $experimentImporter->setFilter($filter);
            $experimentImporter->import($filePath);

            // save comparisons for pooling
            $comparisons = array_merge($comparisons, $experimentImporter->getComparisons());
        }

        // do pooling now that we have all experiments imported
        $poolers = array(
            new StrainPooler(),
//            new GenotypePooler(),
//            new GenotypeMixedMatingPooler(),
        );
        $sampleService = new SampleService($this->pdo);
        $comparisonService = new ComparisonService($this->pdo);
        foreach ($poolers as $pooler) {
            $pooledComparisons = $pooler->getPooledComparisons($comparisons);
            foreach ($pooledComparisons as $comparison) {
                $sampleService->save($comparison->getTestSample());
                $sampleService->save($comparison->getReferenceSample());
                $comparisonService->save($comparison);
            }
        }

        // do R analysis of comparisons
        $this->comparisonAnalyzer->run();

        // udpate build meta data
        $stmt = $this->pdo->prepare('INSERT INTO build_meta (created_at) VALUES (?)');
        $stmt->execute(array(date('Y-m-d H:i:s')));

        // optimize target table indexes
        // NOTE: PDO::exec('OPTIMIZE TABLES...') caused a MySQL buffered query
        // error downstream due to OPTIMIZE TABLES returning a result set. Here
        // we capture the result set in the statement and close the cursor manually
        // without reading results. You can also use $stmt->fetchAll() to clear
        // the results cursor.
        // Related bug report: https://bugs.php.net/bug.php?id=34499
        $stmt = $this->pdo->query('OPTIMIZE TABLES ' . join(', ', $targetTables));
        $stmt->closeCursor();
    }

    /**
     * @param string $folderPath
     * @return array of experiment file names in the given folder
     */
    private function getExperimentFileNames($folderPath) {
        $fileNames = array();
        if (is_dir($folderPath)) {
            $dir = opendir($folderPath);
            while (($fileName = readdir($dir)) !== false) {
                if (preg_match('/^.+\.csv$/', $fileName)) {
                    $fileNames[] = $fileName;
                }
            }
        }
        return $fileNames;
    }

    private function getCitationsFilePath() {
        return $this->inputPath . '/' . self::CITATIONS_FILENAME;
    }

    private function getStrainsFilePath() {
        return $this->inputPath . '/' . self::STRAINS_FILENAME;
    }

    private function getFilterFilePath() {
        return $this->inputPath . '/' . self::FILTER_FILENAME;
    }

    private function getExperimentsFolderPath() {
        return $this->inputPath . '/' . self::EXPERIMENTS_FOLDER;
    }
}