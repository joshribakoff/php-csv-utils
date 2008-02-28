<?php

require_once 'Csv/Exception.php';

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
        $i = 0; $data = '';
        foreach ($rows as $row) {
            if ($i > 10) break;
            $data .= sprintf("%s\n", $row);
            $i++;
        }
        $sniffer = new Csv_Sniffer();
        $dialect = $sniffer->sniff($data);
        $this->assertIsA($dialect, 'Csv_Dialect');
        $this->assertEqual($dialect->delimiter, "|");
        $this->assertEqual($dialect->quotechar, '');
    
    }
    
    
}