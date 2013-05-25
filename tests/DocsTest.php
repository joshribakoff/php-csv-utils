<?php
/**
 * This test case tests every example in the documentation to make sure they work
 * exactly as shown - part of the release process is to check the docs against this
 * test case so that we can always be sure that our code samples work
 */
class DocsTest extends PHPUnit_Framework_TestCase
{
    protected $tmpfile;
    protected $header = array('id', 'name', 'price', 'description', 'taxable');
    protected $data = array(
        array(1, 'Widget', 10.99, 'A wonderful wittle widget.', 1),
        array(2, 'Whatsamahoozit', 1.99, 'The best Whatsamahoozit this side of Wyoming.', 0),
        array(3, 'Dandy Doodad', 19.99, 'This is one dandy doodad.', 1),
        array(4, 'Thingamajigger', 100, 'Thingamajiggers are the best product known to man.', 1),
        array(5, 'Jolly Junk', 0.99, 'This is just some junk.', 1),
        array(6, 'Something', 40.49, 'I like this. It is something. It isn�t taxable.', 0),
        array(7, 'Cheese Doodles', 500, 'Cheesey and full of all kinds of doodle', 0),
        array(8, 'Monkey Juice', 18, 'Monkey Juice is my favorite kind of juice', 1),
        array(9, 'Plastic Monkeys', 108, 'Not fake, just plastic.', 1),
        array(10, 'Steve', 4.09, 'Hey! Don\'t blame it on Steve!', 0),
    );

    public function setUp()
    {
        $this->tmpfile = sys_get_temp_dir() . '/products.csv';
        $writer = new Csv_Writer($this->tmpfile, new Csv_Dialect(array('quoting' => Csv_Dialect::QUOTE_NONNUMERIC)));
        $writer->writeRows($this->data);
    }

    public function tearDown()
    {
        if (file_exists($this->tmpfile)) unlink($this->tmpfile);
    }

    // 2.2 - reading a csv file
    public function test_2_2_Reading_a_csv_file()
    {
        ob_start();
        $reader = new Csv_Reader($this->tmpfile);
        foreach ($reader as $row) {
            print $row[1] . "<br>";
        }
        $captured = ob_get_clean();
        $this->assertEquals('Widget<br>Whatsamahoozit<br>Dandy Doodad<br>Thingamajigger<br>Jolly Junk<br>Something<br>Cheese Doodles<br>Monkey Juice<br>Plastic Monkeys<br>Steve<br>', $captured);
    }

    /** @todo Luke had this one commented out, figure out why & what to do */
    public function test_2_2_Other_Methods_Of_Looping_Through_A_File()
    {
        ob_start();
        $reader = new Csv_Reader($this->tmpfile);
        $i = 0;
        while (($row = $reader->getRow()) && $i < 5) {
            print $row[1] . "<br>";
            $i++;
        }
        $captured = ob_get_clean();
        $this->assertEquals('Widget<br>Whatsamahoozit<br>Dandy Doodad<br>Thingamajigger<br>Jolly Junk<br>', $captured);
    }
}