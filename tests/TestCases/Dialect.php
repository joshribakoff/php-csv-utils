<?php
/**
 * Csv Writer unit tests
 */
class Test_Of_Csv_Dialect extends UnitTestCase {

    public function setUp() {
    
		
	
    }
	
    public function tearDown() {

    
	
    }
	
    public function test_Csv_Dialect_Can_Accept_Options_Param() {
    
        $dialect = new Csv_Dialect(array('delimiter' => "?", 'quotechar' => '`', 'lineterminator' => "\r\n"));
        $this->assertEqual($dialect->delimiter, "?");
        $this->assertEqual($dialect->quotechar, "`");
        $this->assertEqual($dialect->lineterminator, "\r\n");
    
    }

}
