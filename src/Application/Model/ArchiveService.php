<?php

namespace Application\Model;

class ArchiveService 
{
    private $archivePath;
    
    public function __construct() {
        $this->archivePath = BASE_PATH 
                . DIRECTORY_SEPARATOR . 'data' 
                . DIRECTORY_SEPARATOR . 'archive';
    }

    public function getArchiveFiles() {
        // get archive filenames
        $dir = opendir($this->archivePath);
        $filenames = array();
        while (false !== ($filename = readdir($dir))) {
            if (preg_match('/^.+\.zip$/', $filename)) {
                $filenames[] = $filename;
            }
        }
        rsort($filenames, SORT_STRING);
        
        // build archive file data
        $files = array();
        foreach ($filenames as $filename) {
            $filePath = $this->archivePath . DIRECTORY_SEPARATOR . $filename;
            $files[] = array(
                'filename' => $filename,
                'sizeInMb' => filesize($filePath) / 1024 / 1024,
            );
        }
        return $files;
    }
    
    public function getArchiveFilePath($filename) {
        $filePath = $this->archivePath . DIRECTORY_SEPARATOR . basename($filename);
        if (is_file($filePath)) {
            return $filePath;
        } else {
            return null;
        }
    }

}
