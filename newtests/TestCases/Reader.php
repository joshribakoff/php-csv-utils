<?php
/**
 * Unit tests for all aspects of the Csv Reader
 */
require_once 'Csv/Reader.php';

class Test_Of_Csv_Reader extends UnitTestCase {

    public function testCsvReaderThrowsFileNotFoundExceptionIfFileNotFound() {
    
        $this->expectException(new Csv_Exception_FileNotFound('File does not exist or is not readable: "./data/nonexistant.csv".'));
        $reader = new Csv_Reader('./data/nonexistant.csv');
    
    }

}