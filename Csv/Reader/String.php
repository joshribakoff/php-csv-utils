<?php
class Csv_Reader_String extends Csv_Reader {

    /**
     * 
     */
    public function __construct($string, Csv_Dialect $dialect = null) {
    
        if (is_null($dialect)) {
            //$dialect = $this->autoDetectString($string);
            $dialect = new Csv_Dialect;
        }
        $this->dialect = $dialect;
        // if last character isn't a line-break add one
        $lastchar = substr($string, strlen($string)-1, 1);
        if ($lastchar !== $dialect->lineterminator) $string = $string . $dialect->lineterminator;
        $this->handle = fopen("php://temp", 'w+');
        fwrite($this->handle, $string);
        unset($string);
        if ($this->handle === false) throw new Csv_Exception_FileNotFound('File does not exist or is not readable: "' . $path . '".');
        $this->rewind();
    
    }

}