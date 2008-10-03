<?php
class Csv_Reader_String extends Csv_Reader {

    /**
     * 
     */
    public function __construct($string, Csv_Dialect $dialect = null) {
    
        if (is_null($dialect)) {
            $detecter = new Csv_AutoDetect;
            $dialect = $detecter->detect($string); // will throw an exception if it fails, so we don't need to do anything
            $dialect->lineterminator = "\r\n";
            // $dialect = $this->autoDetectFile($path);
        }
        $this->dialect = $dialect;
        // if last character isn't a line-break add one
        $lastchar = substr($string, strlen($string)-1, 1);
        if ($lastchar !== $dialect->lineterminator) $string = $string . $dialect->lineterminator;
        $this->handle = fopen("php://memory", 'w+'); // not sure if I should use php://memory or php://temp here
        fwrite($this->handle, $string);
        unset($string);
        if ($this->handle === false) throw new Csv_Exception_FileNotFound('File does not exist or is not readable: "' . $path . '".');
        $this->rewind();
    
    }

}