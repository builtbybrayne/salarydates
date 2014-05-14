<?php
/**
 * Created by PhpStorm.
 * User: al
 * Date: 13/05/2014
 * Time: 21:26
 */

namespace Perchten;

use ReflectionClass;
use Carbon\Carbon;

class SDCSVWriterTest extends SDTestBase {

    public function setUp()
    {
        parent::setUp();
    }

    protected function tearDown()
    {
        parent::tearDown();
        rmrdir("tmp");
    }


    public function testCheckFileExistsAndWriteable() {

        // Check we can write to expected directory
        $printConfig = new SDPrintConfig("tmp/sdtest/some/path/to/file.csv");
        $printConfig->autoCreateFiles = false;
        $this->testClass = new SDCSVWriter($printConfig,$this->getQuietSDConfig());
        $this->reflection = new ReflectionClass($this->testClass);

        $this->assertFalse(is_dir("tmp"));
        $this->assertTrue(mkdir("tmp/sdtest/",0744,true));
        $this->assertTrue($this->invokeCheckFileExistsAndWriteable($printConfig->file));

        // Check we fail for bad permissions directory
        $printConfig = new SDPrintConfig("tmp/sdtest/nopermissions/file.csv");
        $printConfig->autoCreateFiles = false;
        $this->testClass = new SDCSVWriter($printConfig,$this->getQuietSDConfig());
        $this->reflection = new ReflectionClass($this->testClass);

        $this->assertTrue(mkdir("tmp/sdtest/nopermissions"),0744);
        $this->assertTrue(chmod("tmp/sdtest/nopermissions",0444));
        $this->assertFalse(is_writable("tmp/sdtest/nopermissions"));
        $this->assertFalse($this->invokeCheckFileExistsAndWriteable($printConfig->file));
    }

    /**
     * @depends testCheckFileExistsAndWriteable
     */
    public function testWrite() {

        $year = 2014;
        $dates = array(
            "0" => array(Carbon::create(2014,1,1)->startOfDay(),Carbon::create(2014,1,2)->startOfDay()),
            "1" => array(Carbon::create(2014,1,3)->startOfDay(),Carbon::create(2014,1,4)->startOfDay())
        );
        $expected = "\"Month (2014)\",\"Salary Date\",\"Bonus Date\"\nJanuary,\"Wed 1/1/2014\",\"Thu 2/1/2014\"\nFebruary,\"Fri 3/1/2014\",\"Sat 4/1/2014\"\n";

        $csvWriter = new SDCSVWriter(new SDPrintConfig("tmp/sdtest.csv"),$this->getQuietSDConfig());
        $csvWriter->write($year,$dates);

        $this->assertEquals($expected,file_get_contents("tmp/sdtest.csv"));
    }

    private function invokeCheckFileExistsAndWriteable($file) {
        return $this->getMethod("checkFileExistsAndWriteable")->invoke($this->testClass,$file);
    }

}
 