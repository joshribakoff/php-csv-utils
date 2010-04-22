<?php

Mock::Generate('Csv_Dialect', 'Mock_Dialect');
Mock::Generate('Csv_Dialect', 'Mock_Dialect_Two');

/**
 * Csv Reader unit tests
 */
class Test_Of_Csv_Reader extends UnitTestCase {

    protected $files = array();
	
    protected $tempfile;
    
    public function setUp() {
    
        $this->files['tab-200'] = './data/tab-200.csv';
        $this->files['pipe-100'] = './data/pipe-100.csv';
        $this->files['comma-200'] = './data/comma-200.csv';
        $this->files['blank-lines-200'] = './data/blank-lines-200.csv';
        $this->files['too-short'] = './data/too-short.csv';
        $this->tempfile = './data/tmp.csv';
    
    }
    
    public function tearDown() {
    
        if (isset($this->tempfile) && file_exists($this->tempfile));
        unset($this->tempfile);
    
    }
    
    /**
     * Csv_Reader should use the default dialect if none is provied (excel for now)
    */
    public function test_Csv_Reader_Uses_AutoDetect_To_Get_Dialect_If_None_Provided() {
    
        $reader = new Csv_Reader($this->files['comma-200']);
        $dialect = $reader->getDialect();
        $this->assertIsA($dialect, 'Csv_Dialect');
        $this->assertEqual($dialect->delimiter, ",");
        
        $reader = new Csv_Reader($this->files['pipe-100']);
        $dialect = $reader->getDialect();
        $this->assertIsA($dialect, 'Csv_Dialect');
        $this->assertEqual($dialect->delimiter, "|");
    
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
        $this->assertEqual($reader->getPath(), $file);
    
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
     * 
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
    
    /** Moved from Csv_AutoDetect **/

    /**
     * 
     */
    public function test_Reader_Automatically_Detects_Dialect() {
    
        $reader = new Csv_Reader('data/pipe-100.csv'); // didnt provide a dialect, so it should detect format
        $dialect = $reader->getDialect();
        $this->assertIsA($dialect, 'Csv_Dialect');
        $this->assertEqual($dialect->delimiter, "|");
        $this->assertEqual($dialect->quotechar, '"');
        $this->assertEqual($dialect->quoting, Csv_Dialect::QUOTE_NONE);
    
    }
    
    public function test_Reader_Throws_Exception_If_Dialect_Cant_Be_Determined() {
    
        $data = "I am a piece of data without|||| any delimiters or anything\nI am another line\n. There is\n no way to determ\nine my
                 format\nsadf asd\nasdf asfadf\nasdl;fkas;lfdkasdf\nasdf as fad\nasdf as asdf\nsad,a dfas,d fasdf
                 I am a piece of data without|||| any delimiters or anything\nI am another line\n. There is\n no way to determ\nine my
                 format\nsadf asd\nasdf asfadf\nasdl;fkas;lfdkasdf\nasdf as fad\nasdf as asdf\nsad,a dfas,d fasdf
                 I am a piece of data without|||| any delimiters or anything\nI am another line\n. There is\n no way to determ\nine my
                 format\nsadf asd\nasdf asfadf\nasdl;fkas;lfdkasdf\nasdf as fad\nasdf as asdf\nsad,a dfas,d fasdf
                 I am a piece of data without|||| any delimiters or anything\nI am another line\n. There is\n no way to determ\nine my
                 format\nsadf asd\nasdf asfadf\nasdl;fkas;lfdkasdf\nasdf as fad\nasdf as asdf\nsad,a dfas,d fasdf
                 I am a piece of data without|||| any delimiters or anything\nI am another line\n. There is\n no way to determ\nine my
                 format\nsadf asd\nasdf asfadf\nasdl;fkas;lfdkasdf\nasdf as fad\nasdf as asdf\nsad,a dfas,d fasdf
                 I am a piece of data without|||| any delimiters or anything\nI am another line\n. There is\n no way to determ\nine my
                 format\nsadf asd\nasdf asfadf\nasdl;fkas;lfdkasdf\nasdf as fad\nasdf as asdf\nsad,a dfas,d fasdf
                 I am a piece of data without|||| any delimiters or anything\nI am another line\n. There is\n no way to determ\nine my
                 format\nsadf asd\nasdf asfadf\nasdl;fkas;lfdkasdf\nasdf as fad\nasdf as asdf\nsad,a dfas,d fasdf";
        $this->expectException(new Csv_Exception_CannotDetermineDialect('Csv_AutoDetect was unable to determine the file\'s dialect.'));
        $reader = new Csv_Reader_String($data);
    
    }
    
    public function test_Detect_Throws_Exception_If_Data_Sample_Too_Short() {
    
        $this->expectException(new Csv_Exception_CannotDetermineDialect('You must provide at least ten lines in your sample data'));
        $reader = new Csv_Reader($this->files['too-short']);
    
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
    
    /** Stuff I should move into Csv_Reader_String tests **/
    
    /**
     * Tests that you can loop through Csv_Reader as if it was an array
     * This is the best way I can think of to test that its iterable
     * Basically I just test that it loops through and gives all good results
     */
    public function test_Csv_Reader_String_Is_Iterable() {
    
        $reader = new Csv_Reader_String(file_get_contents($this->files['comma-200']));
        $correct = 0;
        foreach ($reader as $row) {
            if (count($row) == 12) $correct++;
        }
        $this->assertEqual($correct, 200);
    
    }
    
    public function test_Reader_String() {
    
        $sample = "";
        for ($i = 0; $i < 10; $i++) {
            $sample .= "this,is,some,test,data,$i\r\n";
        }
        $reader = new Csv_Reader_String($sample);
        $this->assertEqual($reader->count(), 10);
    
    }
    
    public function test_Set_Header() {
    
        // the comma-200 file doesn't have a header, so it will be indexed numerically
        $reader = new Csv_Reader($this->files['comma-200']);
        $header = array('name', 'date', 'email', 'address_1', 'city', 'state', 'zip', 'country', 'phone', 'fax', 'keywords', 'order_id');
        $reader->setHeader($header);
        $row = $reader->getRow();
        $this->assertEqual(array_keys($row), $header);
        $row = $reader->current();
        $this->assertEqual(array_keys($row), $header);
        $row = $reader->next();
        $this->assertEqual(array_keys($row), $header);
        $allrows = $reader->toArray();
        $this->assertEqual(array_keys(current($allrows)), $header);
    
    }
    
    /**
     * Header should be detected automatically by Csv_AutoDetect, but if you want to be
     * absolutely sure that the reader knows there's a header, you can call $reader->hasHeader(true)
     * and it will ignore the dialect

    public function test_Reader_DetectHeader() {
    
        // tab-200 has a header, so it should detect that
        $reader = new Csv_Reader($this->files['tab-200']);
        // turn on auto detect - if the file has a header, 
        $reader->detectHeader();
        
        // comma-200 doesn't have a header, so it should detect that
        $reader = new Csv_Reader($this->files['tab-200']);
        // 
        $reader->detectHeader();
        
    
    }
     */
}
