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
	
		//pre($this->auto->isNonNumeric('1239857'));
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
	
	public function testAutoDetectCanDetectHeader() {
	
		$this->assertTrue($this->auto->hasHeader(file_get_contents('./data/tab-header.csv')));
		$this->assertFalse($this->auto->hasHeader(file_get_contents('./data/comma-200.csv')));
	
	}
	
	public function testAutoDetectCanDetectQuotingStyle() {
	
		$dialect = $this->auto->detect(file_get_contents('./data/tab-quote-none.csv'));
		$this->assertEqual($dialect->quoting, Csv_Dialect::QUOTE_NONE);
		
		$dialect = $this->auto->detect(file_get_contents('./data/comma-quote-minimal.csv'));
		$this->assertEqual($dialect->quoting, Csv_Dialect::QUOTE_MINIMAL);
		
		$dialect = $this->auto->detect(file_get_contents('./data/tab-quote-all.csv'));
		$this->assertEqual($dialect->quoting, Csv_Dialect::QUOTE_ALL);
		
		$dialect = $this->auto->detect(file_get_contents('./data/tab-quote-nonnumeric.csv'));
		$this->assertEqual($dialect->quoting, Csv_Dialect::QUOTE_NONNUMERIC);
	
	}
	
	public function testAutoDetectCanDetectEscapeChar() {
	
		
	
	}
	
	public function testAutoDetectCanDetectLineTerminator() {
	
		
	
	}
	
	public function testAutoDetectCanDetectQuoteChar() {
	
		
	
	}

}
