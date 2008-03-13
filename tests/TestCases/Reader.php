<?php

Mock::Generate('Csv_Dialect', 'Mock_Dialect');
Mock::Generate('Csv_Dialect', 'Mock_Dialect_Two');

require_once 'Csv/Dialect/Excel.php';

/**
 * Csv Reader unit tests
 */
class Test_Of_Csv_Reader extends UnitTestCase
{
    protected $files = array();
    protected $tempfile;
    public function setUp() {
    
        $this->files['tab-200'] = './data/tab-200.csv';
        $this->files['pipe-100'] = './data/pipe-100.csv';
        $this->files['comma-200'] = './data/comma-200.csv';
        $this->files['blank-lines-200'] = './data/blank-lines-200.csv';
        $this->tempfile = './data/tmp.csv';
    
    }
    public function tearDown() {
    
        if (isset($this->tempfile) && file_exists($this->tempfile));
        unset($this->tempfile);
    
    }
    /**
     * Csv_Reader should use the default dialect if none is provied (excel for now)
    */
    public function test_Csv_Reader_Uses_Default_Dialect_If_None_Provided() {
    
        $reader = new Csv_Reader($this->files['comma-200']);
        $this->assertIsA($reader->getDialect(), 'Csv_Dialect');
    
    }
    /**
     * Csv_Reader should also be able to accept a csv dialect in its constructor or by setDialect()
    */
    public function test_Csv_Reader_Accepts_Custom_Dialect() {
    
        $reader = new Csv_Reader($this->files['comma-200'], new Mock_Dialect());
        $this->assertIsA($reader->getDialect(), 'Csv_Dialect');
        
        $reader->setDialect(new Mock_Dialect_Two());
        $this->assertIsA($reader->getDialect(), 'Mock_Dialect_Two');
    
    }
    /**
     * Csv_Reader is an array-like object, so you should be able to count it
     */
    public function test_Csv_Reader_Count() {
    
        $reader = new Csv_Reader($this->files['comma-200']);
        $this->assertEqual(count($reader), 200);
        $this->assertEqual($reader->count(), 200);
    
    }
    /**
     * We should get back the path to the csv file if the csv file exists
     */
    public function test_Csv_Reader_Get_Path() {
    
        $file = './data/tab-200.csv';
        $reader = new Csv_Reader($this->files['tab-200']);
        $this->assertEqual($reader->getPath(), realpath($file));
    
    }
    /**
     * Tests that escape characters are removed from data
     */
    public function test_Csv_Reader_Escape_Characters_Get_Removed() {
    
        $dialect = new Csv_Dialect_Excel();
        $escape_removed_row = array (
            'Denton Kaufman',
            '1/20/2007',
            '70057',
            '8962 Enim. St.',
            'Corpus Christi',
            'AZ',
            '772',
            'Maldives',
            '',
            '',
            'dictum placerat, augue. Sed molestie. Sed id risus quis diam l"uctus lobortis. Class aptent taciti sociosqu ad',
            '1002',
        );
        $reader = new Csv_Reader($this->files['comma-200']);
        $this->assertEqual($reader->current(), $escape_removed_row);
    
    }
    /**
     * Tests that if the delimiter is set properly, rows are counted properly
     */
    public function test_Csv_Reader_Reads_Row() {
    
        $reader = new Csv_Reader($this->files['comma-200']);
        $this->assertEqual(count($reader->current()), 12);
    
    }
    /**
     * Tests that you can loop through Csv_Reader as if it was an array
     * This is the best way I can think of to test that its iterable
     * Basically I just test that it loops through and gives all good results
     */
    public function test_Csv_Reader_Is_Iterable() {
    
        $reader = new Csv_Reader($this->files['comma-200']);
        $correct = 0;
        foreach ($reader as $row) {
        
            if (count($row) == 12) $correct++;
        
        }
        $this->assertEqual($correct, 200);
    
    }
    /**
     * If the file doesn't exist, we should throw an exception
     */
    public function test_Csv_Reader_File_Does_Not_Exist() {
    
        $path = './data/non-existant.csv';
        $this->expectException(new Csv_Exception_FileNotFound('File does not exist or is not readable: "' . $path . '".'));
        $reader = new Csv_Reader($path);
    
    }
    /**
     * Check that blank lines are properly skipped
     */
    public function test_Csv_Reader_Removes_Blank_Lines() {
    
        $reader = new Csv_Reader($this->files['blank-lines-200']);
        foreach ($reader as $row) continue;
        $this->assertEqual($reader->getSkippedLines(), 13);
    
    }
    /**
     * Test that class is capable of maintaining its state / position 
     */
    public function test_Csv_Reader_Maintains_State() {
    
        $reader = new Csv_Reader($this->files['comma-200']);
        // these should not be equal because getRow() should advance the pointer and the next call should grab the next line
        $this->assertNotEqual($reader->getRow(), $reader->getRow());
        // since we have called current() twice, the loop should start from the third line
        $lines = 0;
        while ($reader->getRow()) {
            $lines++;
        }
        $this->assertEqual($lines, 198);
    
    }
    /*
    public function test_Csv_Reader_Throws_Exception_On_Corrupt_Row() {
    
        $data = "\r\n\r\n234324234234,234,234,234,\r\n\r\nasdf,435,\r\n";
        file_put_contents($this->tempfile, $data);
        $reader = new Csv_Reader($this->tempfile);
        $this->expectException(new Csv_Exception('Invalid format for row 3'));
        foreach ($reader as $row) {
            // this should cause an exception
        }
    
    }*/
    /**
     * Test that class is capable of maintaining its state / position 
     */
    public function test_quotes_get_removed_from_data() {
    
        
    
    }
    public function test_Count_Rewinds_Reader() {
    
        $reader = new Csv_Reader($this->files['comma-200']);
        count($reader);
        $this->assertEqual($reader->key(), 0);
    
    }
    // test that $reader->toArray() returns an array of all csv data
    // @todo if first param in toArray() is set to true header row is used as keys
    // @todo also needs to ensure that object is rewound after toArray
    public function test_Reader_Can_Return_Data_As_Array() {
    
        $reader = new Csv_Reader($this->files['comma-200']);
        $first = $reader->current(); // grab this for testing later
        $data = $reader->toArray();
        $compare = array();
        foreach ($reader as $row) {
            $compare[] = $row;
        }
        $this->assertEqual($data, $compare);
        $reader->toArray();
        // test that toArray() rewinds after use
        $this->assertEqual($reader->current(), $first);
    
    }
}