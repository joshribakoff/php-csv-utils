<?php
/**
 * Unit tests for all aspects of the Csv Reader
 */
require_once 'Csv/Reader.php';
require_once 'Csv/Exception/CannotDetermineDialect.php';

class Test_Of_Csv_Reader extends UnitTestCase {

    /**
     * Test expected exception - Csv_Exception_FileNotFound
     */
    public function testCsvReaderThrowsFileNotFoundExceptionIfFileNotFound() {
    
        $this->expectException(new Csv_Exception_FileNotFound('File does not exist or is not readable: "./data/nonexistant.csv".'));
        $reader = new Csv_Reader('./data/nonexistant.csv');
    
    }
    /**
     * Test expected exception - Csv_Exception_CannotDetermineDialect()
     */
    public function testCsvReaderThrowsCannotDetermineDialectIfDataTooSmall() {
    
        $this->expectException(new Csv_Exception_CannotDetermineDialect('You must provide at least ten lines in your sample data.'));
        $reader = new Csv_Reader('./data/too-short.csv');
    
    }
    /**
     * Test expected exception - Csv_Exception_CannotDetermineDialect()
     */
    public function testCsvReaderThrowsCannotDetermineDialectIfDataCorrupt() {
    
        //$this->expectException(new Csv_Exception_CannotDetermineDialect('File does not exist or is not readable: "./data/nonexistant.csv".'));
        $reader = new Csv_Reader('./data/corrupt.csv');
    
    }

}