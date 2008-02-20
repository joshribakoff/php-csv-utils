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
    
        $sniffer = new Csv_Sniffer();
        $this->assertIsA($sniffer->sniff(), 'Csv_Dialect');
    
    }
}