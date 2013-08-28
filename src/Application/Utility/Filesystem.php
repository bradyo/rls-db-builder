<?php

namespace Application\Utility;

class Filesystem 
{
    /**
     * Delete a directory and all it's contents
     * @param string $path 
     */
    public static function deleteDirectory($path) {
        $dir = opendir($path);
        while (false !== ($filename = readdir($dir))) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            $path = $path . '/' . $filename;
            if (is_dir($path)) {
                $this->deleteDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($path);
    }
    
}
