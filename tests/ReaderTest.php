<?php
class ReaderTest extends PHPUnit_Framework_TestCase
{
    protected $files = array();

    protected $tempfile;

    public function setUp() {

        $this->files['tab-200'] = __DIR__.'/data/tab-200.csv';
        $this->files['pipe-100'] = __DIR__.'/data/pipe-100.csv';
        $this->files['comma-200'] = __DIR__.'/data/comma-200.csv';
        $this->files['blank-lines-200'] = __DIR__.'/data/blank-lines-200.csv';
        $this->files['too-short'] = __DIR__.'/data/too-short.csv';
        $this->tempfile = __DIR__.'/data/tmp.csv';

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
        $this->assertInstanceOf('Csv_Dialect', $dialect);
        $this->assertEquals(",", $dialect->delimiter);

        $reader = new Csv_Reader($this->files['pipe-100']);
        $dialect = $reader->getDialect();
        $this->assertInstanceOf('Csv_Dialect', $dialect);
        $this->assertEquals("|", $dialect->delimiter);

    }
}