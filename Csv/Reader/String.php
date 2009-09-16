<?php
require_once 'Csv/Reader.php';
class Csv_Reader_String extends Csv_Reader {

    /**
     * Build a Csv Reader from a string. 
	 * @todo I am beginning to think that the default should be that it accepts a string. 
     */
    public function __construct($string, Csv_Dialect $dialect = null) {
    
        if (is_null($dialect)) {
            // if dialect isn't specified in the constructor, the reader will attempt to figure out the format
			$autodetect = new Csv_AutoDetect();
			$dialect = $autodetect->detect($string);
        }
        $this->dialect = $dialect;
        // if last character isn't a line-break add one
        $lastchar = substr($string, strlen($string) - 1, 1);
        if ($lastchar !== $dialect->lineterminator) $string = $string . $dialect->lineterminator;
        $this->handle = fopen("php://memory", 'w+'); // not sure if I should use php://memory or php://temp here
        fwrite($this->handle, $string);
        if ($this->handle === false) throw new Csv_Exception_FileNotFound('File does not exist or is not readable: "' . $path . '".');
        $this->rewind();
    
    }

}