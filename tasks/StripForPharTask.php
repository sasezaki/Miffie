<?php
require_once "phing/Task.php";

class StripForPharTask extends Task {

    /**
    * The Directory to remove
    */
    private $dir = null;

    public function setDir($str) {
        $this->dir = $str;
    }

    public function main() {
        foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->dir)) as $file) {
            if ($file->isFile() && substr($file, -4) == '.php') {
                //$contents = file_get_contents($file);
                //$contents = self::stripComments($contents);
                $contents = php_strip_whitespace($file);
                $fh = fopen($file, 'w');
                fwrite($fh, $contents);
                fclose($fh);
            }
        }
    }

    /**
     * borrowed from Symfony\Component\ClassLoader\ClassCollectionLoader
     */
    static private function stripComments($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (!in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= $token[1];
            }
        }

        // replace multiple new lines with a single newline
        $output = preg_replace(array('/\s+$/Sm', '/\n+/S'), "\n", $output);

        return $output;
    }
}
