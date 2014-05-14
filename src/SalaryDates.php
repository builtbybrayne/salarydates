<?php
namespace Perchten;

use Carbon\Carbon;
use Monolog\Logger;
use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;

include "SDInit.php";

class SalaryDates {

    private $SDConfig;
    private $printConfig;
    private $year;
    private $logger;

    public function __construct(){
        global $argv;

        $defaultYear = Carbon::now()->year;
        $defaultFile = truepath("./salarydates_<year>.csv");


        $getopt = new Getopt(array(
            (new Option("d","debug"))->setDescription("Print debug information to the console."),
            (new Option("f","file",Getopt::REQUIRED_ARGUMENT))->setDescription("Set the output file, which will be overwritten if it exists. Default: ".$defaultFile)->setDefaultValue($defaultFile),
            (new Option("h","help"))->setDescription("Prints help details."),
            (new Option("p","print"))->setDescription("Print the csv output to the console."),
            (new Option("q","quiet"))->setDescription("Suppress all but the most critical or fatal messages. Explicitly requested printouts via the -p flag are unaffected."),
            (new Option("t","dateformat"))->setDescription("Specify the date format. Default: D j/n/Y (i.e. Mon 28/2/2012)")->setDefaultValue("D j/n/Y"),
            (new Option("v","verbose"))->setDescription("Print additional information to the console."),
            (new Option("y","year",Getopt::REQUIRED_ARGUMENT))->setDescription("Set the year. Default: Current year (".$defaultYear.")")->setDefaultValue($defaultYear)
        ));

        if ( count($argv) == 1 ) {
            echo "No arguments specified. Use -h flag for help options. \n\n--\tUsing default year ".$defaultYear." and outputfile ".$defaultFile.". \n\nAre you sure you want to continue? [YES/no]  ";
            $handle = fopen ("php://stdin","r");
            $line = strtolower(trim(fgets($handle)));
            if( !( $line == '' || $line=="yes" || $line=="y" ) ){
                echo $getopt->getHelpText();
                exit;
            }
            echo "\n";
        }


        $getopt->parse();




        $this->SDConfig = new SDConfig();
        $this->SDConfig->verbose = $getopt->getOption("v")?:$getopt->getOption("d");
        $this->SDConfig->debug = $getopt->getOption("d");
        $this->SDConfig->quiet = $getopt->getOption("q");

        $this->printConfig = new SDPrintConfig();
        $this->printConfig->file = truepath($getopt->getOption("f"));
        $this->printConfig->printtoconsole = $getopt->getOption("p");
        $this->printConfig->dateformat = $getopt->getOption("t");

        $this->year = $getopt->getOption("y");
        $this->printConfig->file = preg_replace("/<year>/",$this->year,$this->printConfig->file);


        $this->logger = SDBase::getLogger(get_class(),$this->SDConfig->verbose,$this->SDConfig->debug);
        $this->logger->addDebug("Config is: \n".neat_html(array(
                "Year" => $this->year,
                "File" => $this->file,
                "Print" => $this->print,
                "SDConfig" => $this->SDConfig),"return,nopre"
            ));

        if ( $getopt->getOption("h") ) {
            echo $getopt->getHelpText();
            exit;
        }

    }

    public function run() {

        $dateCalculator = new SDDateCalculator($this->SDConfig);
        $dateCalculator->setYear($this->year);

        $csvWriter = new SDCSVWriter($this->printConfig,$this->SDConfig);
        $csvWriter->write($dateCalculator->getYear(),$dateCalculator->getDates());
    }

}




