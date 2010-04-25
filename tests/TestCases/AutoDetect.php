<?php
/**
 * Csv Writer unit tests
 */
class Test_Of_Csv_AutoDetect extends UnitTestCase {

	public function setUp() {
	
		$this->auto = new Csv_AutoDetect;
	
	}
	
	public function tearDown() {
	
		
	
	}
	
	/**
	 * At least for now, spaces are not allowed as a dilimiter. Until I find
	 * a case where that would be necessary, it will stay that way.
	 */
	public function testAutoDetectCannotUseSpaceAsDelimiter() {
	
		$dialect = $this->auto->detect(file_get_contents('./data/space-200.csv'));
		$this->assertNotEqual($dialect->delimiter, ' ');
	
	}
	
	public function testAutoDetectCanDetectDelimiter() {
	
		$dialect = $this->auto->detect(file_get_contents('./data/comma-200.csv'));
		$this->assertEqual($dialect->delimiter, ',');
		$dialect = $this->auto->detect(file_get_contents('./data/tab-200.csv'));
		$this->assertEqual($dialect->delimiter, "\t");
		$dialect = $this->auto->detect(file_get_contents('./data/pipe-100.csv'));
		$this->assertEqual($dialect->delimiter, '|');
	
	}
	

}
