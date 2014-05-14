<?php
namespace Perchten;

use Carbon\Carbon;
use DateTime;
use Perchten\SDTestBase;

class SDDateCalculatorTest extends SDTestBase {

    public function setUp()
    {
        $this->testClass = new SDDateCalculator($this->getQuietSDConfig());
        parent::setUp();
    }


    public function testParseYear() {

        $currentYear = Carbon::now()->year;

        // test default to current year
        $this->assertEquals($currentYear,$this->invokeParseYear());

        // test specific year overrides
        $this->assertEquals(2010,$this->invokeParseYear("2010"));
        $this->assertEquals(2011,$this->invokeParseYear(2011));
        $this->assertEquals(2012,$this->invokeParseYear(new DateTime('2012-01-01')));

        // test bad inputs
        $this->assertEquals($currentYear,$this->invokeParseYear("blah 2020 blah blah"));
        $this->assertEquals($currentYear,$this->invokeParseYear(123456789));
        $this->assertEquals($currentYear,$this->invokeParseYear(array("1999","xxx")));

    }

    public function testSetYear() {

        $currentYear = Carbon::now()->year;

        $dc = new SDDateCalculator($this->getQuietSDConfig());
        $dc->setYear();
        $this->assertEquals($currentYear,$dc->getYear());

        $dc->setYear("2012");
        $this->assertEquals("2012",$dc->getYear());
    }

    public function testGetBasicSalaryDate() {

        // last day in Jan 2014 is a weekday
        $jan = Carbon::create(2014,1,31)->startOfDay();
        $this->assertTrue($jan->eq($this->invokeGetBasicSalaryDate(2014,0)));

        // last day in Nov 2013 is a weekend. Last Friday is the 29th
        $nov = Carbon::create(2013,11,29)->startOfDay();
        $this->assertTrue($nov->eq($this->invokeGetBasicSalaryDate(2013,10)));

        // last day in May 2014 is a weekend so should not match last day
        $may = Carbon::create(2014,5,31)->startOfDay();
        $this->assertFalse($may->eq($this->invokeGetBasicSalaryDate(2014,4)));
    }

    public function testGetBonusDate() {
        // March 2014 has the 15th on a Saturday, so February's bonus day should return 19th March
        $mar = Carbon::create(2014,3,19)->startOfDay();
        $this->assertTrue($mar->eq($this->invokeGetBonusDate(2014,1)));

        // Jan 2014 has 15th on a Wednesday, so Dec 2013 bonus day should be Jan 15th 2014
        $jan = Carbon::create(2014,1,15)->startOfDay();
        $this->assertTrue($jan->eq($this->invokeGetBonusDate(2013,11)));
    }

    public function testGetDates() {

        // Manually defined dates for 2014
        $wanted = array(
            0 => array(Carbon::create(2014,1,31)->startOfDay(),Carbon::create(2014,2,19)->startOfDay()),
            1 => array(Carbon::create(2014,2,28)->startOfDay(),Carbon::create(2014,3,19)->startOfDay()),
            2 => array(Carbon::create(2014,3,31)->startOfDay(),Carbon::create(2014,4,15)->startOfDay()),
            3 => array(Carbon::create(2014,4,30)->startOfDay(),Carbon::create(2014,5,15)->startOfDay()),
            4 => array(Carbon::create(2014,5,30)->startOfDay(),Carbon::create(2014,6,18)->startOfDay()),
            5 => array(Carbon::create(2014,6,30)->startOfDay(),Carbon::create(2014,7,15)->startOfDay()),
            6 => array(Carbon::create(2014,7,31)->startOfDay(),Carbon::create(2014,8,15)->startOfDay()),
            7 => array(Carbon::create(2014,8,29)->startOfDay(),Carbon::create(2014,9,15)->startOfDay()),
            8 => array(Carbon::create(2014,9,30)->startOfDay(),Carbon::create(2014,10,15)->startOfDay()),
            9 => array(Carbon::create(2014,10,31)->startOfDay(),Carbon::create(2014,11,19)->startOfDay()),
            10 => array(Carbon::create(2014,11,28)->startOfDay(),Carbon::create(2014,12,15)->startOfDay()),
            11 => array(Carbon::create(2014,12,31)->startOfDay(),Carbon::create(2015,1,15)->startOfDay()),
        );

        $dc = new SDDateCalculator($this->getQuietSDConfig());
        $dc->setYear(2014);
        foreach ( $dc->getDates() as $month => $dates) {
            $this->assertTrue(is_array($dates));
            $this->assertTrue(count($dates)==2);
            $this->assertTrue($wanted[$month][0]->eq($dates[0]));
            $this->assertTrue($wanted[$month][1]->eq($dates[1]));
        }

    }

    private function invokeParseYear($year=null) {
        return $this->getMethod("parseYear")->invoke($this->testClass,$year);
    }
    private function invokeGetBasicSalaryDate($year,$month) {
        return $this->getMethod("getBasicSalaryDate")->invoke($this->testClass,$year,$month);
    }
    private function invokeGetBonusDate($year,$month) {
        return $this->getMethod("getBonusDate")->invoke($this->testClass,$year,$month);
    }



}