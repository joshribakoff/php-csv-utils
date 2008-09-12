<?php

require_once 'Csv/Exception.php';
require_once 'Csv/Exception/CannotDetermineDialect.php';

/**
 * Csv Writer unit tests
 */
class Test_Of_Csv_AutoDetect extends UnitTestCase
{
    public function setUp() {
    
        
    
    }
    public function tearDown() {
    
        
    
    }
    /**
     * 
     */
    public function test_Detect_Method_Returns_Csv_Dialect() {
    
        $rows = file('data/pipe-100.csv');
        $data = implode("", array_slice($rows, 0, 20));
        $detecter = new Csv_AutoDetect();
        $dialect = $detecter->detect($data);
        $this->assertIsA($dialect, 'Csv_Dialect');
        $this->assertEqual($dialect->delimiter, "|");
        $this->assertEqual($dialect->quotechar, '"');
        $this->assertEqual($dialect->quoting, Csv_Dialect::QUOTE_NONE);
    
    }
    
    public function test_Detect_Throws_Exception_If_Dialect_Cant_Be_Determined() {
    
        $data = "I am a piece of data without|||| any delimiters or anything\nI am another line\n. There is\n no way to determ\nine my
                 format\nsadf asd\nasdf asfadf\nasdl;fkas;lfdkasdf\nasdf as fad\nasdf as asdf\nsad,a dfas,d fasdf";
        $this->expectException(new Csv_Exception_CannotDetermineDialect('Csv_AutoDetect was unable to determine the file\'s dialect.'));
        $detecter = new Csv_AutoDetect();
        $detecter->detect($data);
    
    }
    
    public function test_Detect_Throws_Exception_If_Data_Sample_Too_Short() {
    
        $data = "I am a piece of data without|||| any delimiters or anything";
        $this->expectException(new Csv_Exception_DataSampleTooShort('You must provide at least ten lines in your sample data'));
        $detecter = new Csv_AutoDetect();
        $detecter->detect($data);
    
    }
    
    public function test_Detect_Can_Detect_Header() {
    
        $data = file(realpath('data/tab-200.csv'));
        $sample1 = implode("", array_slice($data, 0, 20));
        $sample2 = implode("", array_slice($data, 1, 21));
        $sample3 = implode("\n", file(realpath("data/excel-formatted.csv")));
        $sample4 = implode("", file(realpath("data/pipe-100.csv")));
        $detecter = new Csv_AutoDetect();
        $this->assertTrue($detecter->hasHeader($sample1));
        $this->assertFalse($detecter->hasHeader($sample2));
        $this->assertFalse($detecter->hasHeader($sample3));
        $this->assertTrue($detecter->hasHeader($sample4));
    
    }
    public function test_Detect_Doesnt_Use_More_Than_Twenty_Lines() {
    
        //$file1 = file(realpath('data/tab-200.csv'));
    
    }
    public function test_Detect_Can_Accept_String_Or_Any_Csv_Reader() {
    }
    
}