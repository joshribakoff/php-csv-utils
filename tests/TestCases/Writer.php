<?php

require_once 'Csv/Exception/CannotAccessFile.php';

Mock::Generate('Csv_Dialect', 'Mock_Dialect');
Mock::Generate('Csv_Dialect', 'Mock_Dialect_Two');

/**
 * Csv Writer unit tests
 */
class Test_Of_Csv_Writer extends UnitTestCase
{
    public function setUp() {
    
        $this->dialect = new Mock_Dialect;
        $this->dialect->delimiter = ",";
        $this->dialect->quotechar = '"';
        $this->dialect->escapechar = "\\";
        $this->dialect->skipblanklines = true;
        $this->dialect->lineterminator = "\r\n";
        
        $this->file = realpath('../') . '/tests/data/writer1.csv';
        $this->file2 = realpath('../') . '/tests/data/writer2.csv';
        $this->file3 = realpath('../') . '/tests/data/writer3.csv';
        
        $this->testdata = array(
        
            array(
                'This contains the quote "character" because it\'s cool like that',
                1,
                2,
            ),
            array(
                "This one contains some commas, and some \t\t tabs and stuff",
                45,
                2009,
            ),
            array(
                'I have "several" different kinds of \'quotes\'',
                133,
                2234234,
            ),
            array(
                "I am regular\n\r text but with a line break in me",
                21,
                "Num123412342",
            ),
        
        );
    
    }
    public function tearDown() {
    
        if (file_exists($this->file)) unlink($this->file);
        if (file_exists($this->file2)) unlink($this->file2);
        if (file_exists($this->file3)) unlink($this->file3);
    
    }
    /**
     * Writer should create the file if it doesnt exist
     */
    public function test_Csv_Writer_Accepts_Filename_In_Constructor() {
    
        
        $writer = new Csv_Writer($this->file);
        $this->assertEqual($this->file, $writer->getPath());
    
    }
    /**
     * Writer should create the file if it doesnt exist
     */
    public function test_Csv_Writer_Uses_Default_Dialect() {
    
        
        $writer = new Csv_Writer($this->file);
        $this->assertIsA($writer->getDialect(), 'Csv_Dialect');
    
    }
    /**
     * Csv_Writer should also be able to accept a csv dialect in its constructor or by setDialect()
    */
    public function test_Csv_Writer_Accepts_Custom_Dialect() {
    
        $reader = new Csv_Writer($this->file, new Mock_Dialect());
        $this->assertIsA($reader->getDialect(), 'Csv_Dialect');
        
        $reader->setDialect(new Mock_Dialect_Two());
        $this->assertIsA($reader->getDialect(), 'Mock_Dialect_Two');
    
    }
    /**
     * Writer should create the file if it doesnt exist (the close method will write changes to the db)
     */
    public function test_Csv_Writer_Constructor_Creates_File_If_Nonexistant() {
    
        $this->assertFalse(file_exists($this->file));
        $writer = new Csv_Writer($this->file);
        $writer->close();
        $this->assertTrue(file_exists($this->file));
    
    }
    /**
     * An exception should be thrown if there is an error writing to the csv file
     */
    public function test_Csv_Writer_Throws_Exception_If_Cant_Write() {
    
        $path = './';
        $this->expectException(new Csv_Exception_CannotAccessFile(sprintf('Unable to create/access file: "%s".', $path)));
        $writer = new Csv_Writer($path);
        $writer->close();
    
    }
    /**
     * Test that writeRow writes one row to the file
     */
    public function test_Csv_Writer_Writes_Row() {
    
        $row = array('ONE', 'TWO', 'THREE');
        $writer = new Csv_Writer($this->file2);
        $writer->writeRow($row);
        $writer->close();
        $this->assertEqual(file_get_contents($this->file2), 'ONE,TWO,THREE');
    
    }
    /**
     * Test that all pending changes are written to the file when the object is destroyed
     */
    public function test_Csv_Writer_Writes_Row_When_Destroyed() {
    
        $row = array('ONE', 'TWO', 'THREE');
        $writer = new Csv_Writer($this->file3);
        $writer->writeRow($row);
        unset($writer);
        $this->assertEqual(file_get_contents($this->file3), 'ONE,TWO,THREE');
    
    }
    public function test_Csv_Writer_Does_Not_Throw_Exception_If_Closed_Then_Destroyed() {
    
        $row = array('ONE', 'TWO', 'THREE');
        $writer = new Csv_Writer($this->file3);
        $writer->writeRow($row);
        $writer->close();
        unset($writer); // this should not throw an exception
        $this->assertTrue(true);
    
    }
    public function test_Can_Change_delimiter() {

        $dialect = new Mock_Dialect;
        $dialect->delimiter = "#";
        $writer = new Csv_Writer($this->file, $dialect);
        $writer->writeRow(array(1,2,3));
        $writer->close();
        $this->assertEqual(file_get_contents($this->file), '1#2#3');

    }
    /**
     * Test that dialects work correctly with quoting and escaping things
     */
    public function test_Dialect_Quote_None() {
    
        $this->dialect->quoting = Csv_Dialect::QUOTE_NONE;
        
        $writer = new Csv_Writer($this->file, $this->dialect);
        $writer->writeRow($this->testdata[0]);
        $writer->close();
        $this->assertEqual('This contains the quote "character" because it\'s cool like that,1,2', file_get_contents($this->file));
    
    }
    /**
     * 
     */
    public function test_Dialect_Quote_All() {
    
        $this->dialect->quoting = Csv_Dialect::QUOTE_ALL;
        $writer = new Csv_Writer($this->file, $this->dialect);
        $writer->writeRow($this->testdata[0]);
        $writer->close();
        $this->assertEqual('"This contains the quote \\"character\\" because it\'s cool like that","1","2"', file_get_contents($this->file));
    
    }
    /**
     * 
     */
    public function test_Dialect_Quote_Minimum() {
    
        $this->dialect->quoting = Csv_Dialect::QUOTE_MINIMAL;
        $writer = new Csv_Writer($this->file, $this->dialect);
        $writer->writeRow($this->testdata[0]);
        $writer->close();
        // check that quotes cause text to be quoted
        $this->assertEqual('"This contains the quote \\"character\\" because it\'s cool like that",1,2', file_get_contents($this->file));
        
        // check that line breaks cause text to be quoted
        $writer = new Csv_Writer($this->file2, $this->dialect);
        $writer->writeRow($this->testdata[3]);
        $writer->close();
        $this->assertEqual('"I am regular' . "\n\r" . ' text but with a line break in me",21,Num123412342', file_get_contents($this->file2));
        
        // check that commas (if they are the delim char) cause text to be quoted
        $writer = new Csv_Writer($this->file3, $this->dialect);
        $writer->writeRow($this->testdata[1]);
        $writer->close();
        $this->assertEqual("\"This one contains some commas, and some \t\t tabs and stuff\",45,2009", file_get_contents($this->file3));
    
    }
    /**
     * 
     */
    public function test_Dialect_Quote_Nonnumeric() {
    
        $this->dialect->quoting = Csv_Dialect::QUOTE_NONNUMERIC;
        $writer = new Csv_Writer($this->file, $this->dialect);
        $writer->writeRow($this->testdata[3]);
        $writer->close();
        $this->assertEqual('"I am regular' . "\n\r" . ' text but with a line break in me",21,"Num123412342"', file_get_contents($this->file));
    
    }
    /**
     * Test that writer accepts a multi-dimensional array of rows to write to disk
     */
    public function test_Writer_Accepts_Array() {
    
        $writer = new Csv_Writer($this->file);
        $data = array(
            array(1,2,3),
            array(4,5,6),
            array(7,8,9),
        );
        $writer->writeRows($data);
        $writer->close(); // write data
        $this->assertEqual("1,2,3\r\n4,5,6\r\n7,8,9", file_get_contents($this->file));
    
    }
    /**
     * Test that writer accepts a reader object to read from instead of an array
     * @todo For some reason not having a line-ending char causes the reader to not read the last line - find out why
     */
    public function test_Writer_WriteRows_Accepts_Reader() {
    
        file_put_contents($this->file, "1,2,3\r\n4,5,6\r\n7,8,9\r\n"); // test csv file
        $reader = new Csv_Reader($this->file);
        $writer = new Csv_Writer($this->file2);
        $dialect = $writer->getDialect();
        $dialect->delimiter = "\t";
        $writer->setDialect($dialect);
        $writer->writeRows($reader);
        $writer->close(); // write data
        $this->assertEqual("1\t2\t3\r\n4\t5\t6\r\n7\t8\t9", file_get_contents($this->file2));
    
    }
    /**
     * @todo When appending a file, there should be a way to detect if you need to prepend a newline char
     *       maybe check to see if the last char in the file is a newline char and if not, prepend one?
     */
    public function test_Writer_Accepts_Handle() {
    
        $content = "1,2,3\r\n4,5,6\r\n7,8,9\r\n";
        file_put_contents($this->file, $content);
        $file = fopen($this->file, 'ab');
        $writer = new Csv_Writer($file);
        $writer->writeRow(array(10,11,12));
        $writer->close();
        $this->assertEqual(file_get_contents($this->file), $content . "10,11,12");
    
    }
}