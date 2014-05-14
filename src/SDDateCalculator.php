<?php
namespace Perchten;
use Carbon\Carbon;
use Monolog\Logger;

/**
 * Class DateCalculator will calculate the salary and bonus dates for a company following these rules:
 *
 * - Sales staff get a regular monthly fixed base salary and a monthly bonus.
 * - The base salaries are paid on the last day of the month unless that day is a Saturday or a Sunday (weekend) in which case they are paid on the friday before.
 * - On the 15th of every month bonuses are paid for the previous month, unless that day is a weekend. In that case, they are paid the first Wednesday after the 15th.
 *
 * USAGE:
 *
 * Static:
 *
 *  DateCalculator::get([$year]);
 *
 *
 * Object:
 *
 *  $datecalculator = new DateCalculator([$year]);
 *  $datecalculator->setYear([$year]); // recalculate for another year
 *  $dates = $datecalculator->getDates();
 *  $year = $datecalculator->getYear();
 *
 *
 */
class SDDateCalculator extends SDBase {

    private $year;
    private $dates;


    /**
     * Optionally specify a year for the object, otherwise the current year will be used.
     *
     * @param String|Int|DateTime $year (optional)
     */
    public function __construct(SDConfig $SDConfig=null)
    {
        parent::__construct($SDConfig);
    }

    public function setYear($year=null) {
        $this->year = $this->parseYear($year);
        $this->logger->addDebug("Year set to ".$this->year);
        $this->calculateDates($this->year); // whenever we update the year, we need to recalculate the salary dates automatically
    }

    public function getYear() {
        return $this->year;
    }

    /**
     * Return the dates calculated for the current year.
     *
     * Output is in the format
     *
     *  array[<month>] = array(<base salary date>,<bonus date>)
     *
     * <month> is 0 indexed
     *
     * @return array
     */
    public function getDates() {
        return $this->dates;
    }

    /**
     * Generates the array of dates for each month in the year, both salary and bonus
     *
     * @param Int $year
     */
    private function calculateDates($year) {
        for ( $month=0;$month<12;$month++ ) {
            $salary = $this->getBasicSalaryDate($year,$month);
            $bonus = $this->getBonusDate($year,$month);
            $this->dates[$month] = array($salary,$bonus);
        }
        $this->logger->info("Dates calculated for ".$year);
        $this->logger->debug("Calculated dates:\n ".print_r($this->dates,true));


    }

    /**
     * Return the last day of the month unless that day is a Saturday or a Sunday (weekend) in which case return the friday before.
     *
     * @param Int $year
     * @param Int $month 0-indexed
     * @return Carbon Date object for salary date (set to start of day)
     */
    private function getBasicSalaryDate($year,$month) {
        $lastDay = Carbon::parse("last day of ".$this->months[$month]." ".$year)->startOfDay();
        return ($lastDay->isWeekday() ) ? $lastDay : Carbon::parse("last friday of ".$this->months[$month]." ".$year)->startOfDay();

    }

    /**
     * Return the 15th of the following month, unless that day is a weekend. In which case, return the first Wednesday after the 15th.
     *
     * @param Int $year
     * @param Int $month (0-indexed)
     * @return Carbon Date object for Bonus date (set to start of day)
     */
    private function getBonusDate($year,$month) {

        // Fix 0-index month and wind forward by one month as bonuses are one month in arrears
        if ( $month == 11 ) {
            $month = 1;
            $year++;
        } else {
            $month += 2;
        }
        $bonusDay = Carbon::create($year,$month,15);
        if ( $bonusDay->dayOfWeek == Carbon::SATURDAY ) return $bonusDay->addDays(4)->startOfDay();
        if ( $bonusDay->dayOfWeek == Carbon::SUNDAY ) return $bonusDay->addDays(3)->startOfDay();
        return $bonusDay->startOfDay();
    }


    /**
     * Determine the year using best guesses from input year, which may be null
     *
     * @param String|Int|DateTime $year (optional)
     * @return the given year or the current year if the input is unparseable
     */
    private function parseYear($year=null) {

        if ( is_string($year) && preg_match('/^[0-9]{4}$/',$year) ) {
            // Given year is a 4-digit string
            $date = Carbon::create(intval($year));
            $this->logger->debug("Year set from string");
        } else if (is_numeric($year) && $year>999 && $year<10000 ) {
            // Given year is a 4-digit number
            $date = Carbon::create(intval($year));
            $this->logger->debug("Year set from int");
        } else if ( is_a($year,"DateTime") ) {
            $date = Carbon::instance($year);
            $this->logger->debug("Year set from DateTime");
        } else {

            // Given year is not parseable or not present
            $date = Carbon::now();
            if ( $year ) {
                // Print an error as we were unable to handle input
                $this->logger->addWarning("Unable to parse '".(string)$year."' as a year. Defaulting to current year ".$date->year.".\n");
                $this->logger->debug("Year unparseable, defaulting");
            } else {
                $this->logger->debug("Year not set, defaulting");
            }
        }
        return $date->year;
    }



}
