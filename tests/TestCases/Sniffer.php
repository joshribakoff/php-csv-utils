<?php

require_once 'Csv/Exception.php';
require_once 'Csv/Exception/CannotDetermineDialect.php';

/**
 * Csv Writer unit tests
 */
class Test_Of_Csv_Sniffer extends UnitTestCase
{
    public function setUp() {
    
        
    
    }
    public function tearDown() {
    
        
    
    }
    /**
     * 
     */
    public function test_Sniff_Method_Returns_Csv_Dialect() {
    
        $rows = file('data/pipe-100.csv');
        $data = implode("", array_slice($rows, 0, 20));
        $sniffer = new Csv_Sniffer();
        $dialect = $sniffer->sniff($data);
        $this->assertIsA($dialect, 'Csv_Dialect');
        $this->assertEqual($dialect->delimiter, "|");
        $this->assertEqual($dialect->quotechar, '');
    
    }
    
    public function test_Sniff_Throws_Exception_If_Dialect_Cant_Be_Determined() {
    
        $data = "I am a piece of data without|||| any delimiters or anything\nI am another line\n. There is\n no way to determ\nine my
                 format\nsadf asd\nasdf asfadf\nasdl;fkas;lfdkasdf\nasdf as fad\nasdf as asdf\nsad,a dfas,d fasdf";
        $this->expectException(new Csv_Exception_CannotDetermineDialect('Csv_Sniffer was unable to determine the file\'s dialect.'));
        $sniffer = new Csv_Sniffer();
        $sniffer->sniff($data);
    
    }
    
    public function test_Sniff_Throws_Exception_If_Data_Sample_Too_Short() {
    
        $data = "I am a piece of data without|||| any delimiters or anything";
        $this->expectException(new Csv_Exception_DataSampleTooShort('You must provide at least ten lines in your sample data'));
        $sniffer = new Csv_Sniffer();
        $sniffer->sniff($data);
    
    }
    
    
}