<?php
class WriterTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {

        $this->dialect = new Csv_Dialect;
        $this->dialect->delimiter = ",";
        $this->dialect->quotechar = '"';
        $this->dialect->escapechar = "\\";
        $this->dialect->skipblanklines = true;
        $this->dialect->lineterminator = "\r\n";

        $this->file = sys_get_temp_dir() . '/writer1.csv';
        $this->file2 = sys_get_temp_dir() . '/writer2.csv';
        $this->file3 = sys_get_temp_dir() . '/writer3.csv';

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

    public function tearDown()
    {
        if (file_exists($this->file)) unlink($this->file);
        if (file_exists($this->file2)) unlink($this->file2);
        if (file_exists($this->file3)) unlink($this->file3);
    }

    public function test_Csv_Writer_Accepts_Filename_In_Constructor()
    {
        $writer = new Csv_Writer($this->file);
        $this->assertEquals($this->file, $writer->getPath());
    }

    public function test_Csv_Writer_Uses_Default_Dialect()
    {
        $writer = new Csv_Writer($this->file);
        $this->assertInstanceOf('Csv_Dialect', $writer->getDialect());
    }

    /**
     * @todo make this test assert something more substantial.
     * Csv_Writer should also be able to accept a csv dialect in its constructor or by setDialect()
     */
    public function test_Csv_Writer_Accepts_Custom_Dialect()
    {
        $reader = new Csv_Writer($this->file, new Csv_Dialect());
        $this->assertInstanceOf('Csv_Dialect', $reader->getDialect());

        $reader->setDialect(new Csv_Dialect());
        $this->assertInstanceOf('Csv_Dialect', $reader->getDialect());
    }

    /**
     * Writer should create the file if it doesnt exist (the close method will write changes to the db)
     */
    public function test_Csv_Writer_Constructor_Creates_File_If_Nonexistant()
    {
        $this->assertFalse(file_exists($this->file));
        $writer = new Csv_Writer($this->file);
        $writer->writeRow(array('one', 'two', 'three'));
        $this->assertTrue(file_exists($this->file));
    }

    /**
     * @expectedException Csv_Exception_FileNotFound
     * An exception should be thrown if there is an error writing to the csv file
     */
    public function test_Csv_Writer_Throws_Exception_If_Cant_Write()
    {
        $writer = new Csv_Writer('./');
        $writer->writeRow(array('one', 'two', 'three'));
    }

    /**
     * Test that writeRow writes one row to the file
     */
    public function test_Csv_Writer_Writes_Row_Immediately()
    {

        $row = array('ONE', 'TWO', 'THREE');
        $writer = new Csv_Writer($this->file2);
        $writer->writeRow($row);
        $this->assertEquals(file_get_contents($this->file2), 'ONE,TWO,THREE' . $writer->getDialect()->lineterminator);

    }

    /**
     * Test that all pending changes are written to the file when the object is destroyed
     */
    public function test_Csv_Writer_Writes_Row_When_Destroyed()
    {

        $row = array('ONE', 'TWO', 'THREE');
        $writer = new Csv_Writer($this->file3);
        $writer->writeRow($row);
        $lineterm = $writer->getDialect()->lineterminator;
        unset($writer);
        $this->assertEquals(file_get_contents($this->file3), 'ONE,TWO,THREE' . $lineterm);

    }

    public function test_Can_Change_delimiter()
    {

        $dialect = new Csv_Dialect;
        $dialect->delimiter = "#";
        $writer = new Csv_Writer($this->file, $dialect);
        $writer->writeRow(array(1, 2, 3));
        $this->assertEquals(file_get_contents($this->file), '1#2#3' . $writer->getDialect()->lineterminator);

    }

    /**
     * Test that dialects work correctly with quoting and escaping things
     */
    public function test_Dialect_Quote_None()
    {

        $this->dialect->quoting = Csv_Dialect::QUOTE_NONE;

        $writer = new Csv_Writer($this->file, $this->dialect);
        $writer->writeRow($this->testdata[0]);
        $this->assertEquals('This contains the quote "character" because it\'s cool like that,1,2' . $writer->getDialect()->lineterminator, file_get_contents($this->file));

    }

    /**
     *
     */
    public function test_Dialect_Quote_All()
    {

        $this->dialect->quoting = Csv_Dialect::QUOTE_ALL;
        $writer = new Csv_Writer($this->file, $this->dialect);
        $writer->writeRow($this->testdata[0]);
        $this->assertEquals('"This contains the quote \\"character\\" because it\'s cool like that","1","2"' . $writer->getDialect()->lineterminator, file_get_contents($this->file));

    }

    /**
     *
     */
    public function test_Dialect_Quote_Minimum()
    {

        $this->dialect->quoting = Csv_Dialect::QUOTE_MINIMAL;
        $writer = new Csv_Writer($this->file, $this->dialect);
        $writer->writeRow($this->testdata[0]);
        // check that quotes cause text to be quoted
        $this->assertEquals('"This contains the quote \\"character\\" because it\'s cool like that",1,2' . $writer->getDialect()->lineterminator, file_get_contents($this->file));

        // check that line breaks cause text to be quoted
        $writer = new Csv_Writer($this->file2, $this->dialect);
        $writer->writeRow($this->testdata[3]);
        $this->assertEquals('"I am regular' . "\n\r" . ' text but with a line break in me",21,Num123412342' . $writer->getDialect()->lineterminator, file_get_contents($this->file2));

        // check that commas (if they are the delim char) cause text to be quoted
        $writer = new Csv_Writer($this->file3, $this->dialect);
        $writer->writeRow($this->testdata[1]);
        $this->assertEquals("\"This one contains some commas, and some \t\t tabs and stuff\",45,2009" . $writer->getDialect()->lineterminator, file_get_contents($this->file3));

    }

    /**
     *
     */
    public function test_Dialect_Quote_Nonnumeric()
    {

        $this->dialect->quoting = Csv_Dialect::QUOTE_NONNUMERIC;
        $writer = new Csv_Writer($this->file, $this->dialect);
        $writer->writeRow($this->testdata[3]);
        $this->assertEquals('"I am regular' . "\n\r" . ' text but with a line break in me",21,"Num123412342"' . $writer->getDialect()->lineterminator, file_get_contents($this->file));

    }

    /**
     * Test that writer accepts a multi-dimensional array of rows to write to disk
     */
    public function test_Writer_Accepts_Array()
    {

        $writer = new Csv_Writer($this->file);
        $data = array(
            array(1, 2, 3),
            array(4, 5, 6),
            array(7, 8, 9),
        );
        $writer->writeRows($data);
        $this->assertEquals("1,2,3" . $writer->getDialect()->lineterminator . "4,5,6" . $writer->getDialect()->lineterminator . "7,8,9" . $writer->getDialect()->lineterminator, file_get_contents($this->file));

    }

    /**
     * Test that writer accepts a reader object to read from instead of an array
     * @todo For some reason not having a line-ending char causes the reader to not read the last line - find out why
     */
    public function test_Writer_WriteRows_Accepts_Reader()
    {

        file_put_contents($this->file, "1,2,3\r\n4,5,6\r\n7,8,9\r\n"); // test csv file
        $reader = new Csv_Reader($this->file, new Csv_Dialect());
        $writer = new Csv_Writer($this->file2);
        $dialect = $writer->getDialect();
        $dialect->delimiter = "\t";
        $writer->setDialect($dialect);
        $writer->writeRows($reader);
        $this->assertEquals("1\t2\t3\r\n4\t5\t6\r\n7\t8\t9" . $writer->getDialect()->lineterminator, file_get_contents($this->file2));

    }

    /**
     * @todo When appending a file, there should be a way to detect if you need to prepend a newline char
     *       maybe check to see if the last char in the file is a newline char and if not, prepend one?
     */
    public function test_Writer_Accepts_Handle()
    {

        $content = "1,2,3\r\n4,5,6\r\n7,8,9\r\n";
        file_put_contents($this->file, $content);
        $file = fopen($this->file, 'ab');
        $writer = new Csv_Writer($file);
        $writer->writeRow(array(10, 11, 12));
        $this->assertEquals(file_get_contents($this->file), $content . "10,11,12" . $writer->getDialect()->lineterminator);

    }

}