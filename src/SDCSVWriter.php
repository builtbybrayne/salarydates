<?php
namespace Perchten;

use Monolog\Logger;
use __;

class SDCSVWriter extends SDBase {

    private $printConfig;

    public function __construct(SDPrintConfig $printConfig,SDConfig $SDConfig=null)
    {
        parent::__construct($SDConfig);
        $this->printConfig = $printConfig;
        if ( $this->printConfig->autoCreateFiles ) {
            $this->ensureFileExists($printConfig->file);
        }
    }

    public function write($year,$dates){

        $p = $this->printConfig->printtoconsole;
        $pout = "";

        $handle = fopen($this->printConfig->file,"w");
        $csvTitle = array("Month (".$year.")","Salary Date","Bonus Date");

        fputcsv($handle,$csvTitle);
        if ($p) $pout .= implode("\t\t",$this->padArray($csvTitle))."\n\n";

        foreach ( $dates as $month => $datePair ) {
            $row = array(
                $this->months[$month],
                $datePair[0]->format($this->printConfig->format),
                $datePair[1]->format($this->printConfig->format)
            );
            $this->logger->addDebug("writing row ".neat_html($row,"return,nopre")." to ".$this->printConfig->file);
            if ( !fputcsv($handle, $row,",",'"') ) {
                throw new \Exception("Failed to write row ".neat_html($row,"return,nopre")." to ".$this->printConfig->file);
            }
            if ($p) $pout .= implode("\t\t",$this->padArray($row))."\n";
        }
        fclose($handle);

        if ( $p ) {
            echo "\n\n".$pout."\n\n\n";
        }
        $this->logger->addNotice("Dates written to ".truepath($this->printConfig->file));

    }

    /**
     * Ensures that the $file to write to exists and is writeable. If it does not exist, it is created
     *
     * @param $file
     * @throws Exception
     */
    private function ensureFileExists($file) {
        $file = truepath($file);
        if ( !$this->checkFileExistsAndWriteable($file) ) {
            $this->logger->addAlert("".$file." cannot be written to. Check the parent folder permissions");
            throw new Exception("".$file." cannot be written to. Check the parent folder permissions");
        }

        $dir = dirname($file);
        if ( !is_dir($dir) && !mkdir($dir,0744,true) ) {
            $this->logger->addAlert("Failed to create ".$dir.".");
            throw new Exception("Failed to create ".$dir.".");
        }
    }

    /**
     * Scan the path to find the first existing parent directory and make sure it is writeable
     *
     * @param $file
     * @return bool True if the $file in question
     */
    private function checkFileExistsAndWriteable($file) {
        if ( is_dir($file) ) {
            $this->logger->debug($file." is ".((is_writable($file))?"writeable":"NOT writeable"));
            return is_writable($file); // If it's a directory, check if it's writeable
        } else {
            return $this->checkFileExistsAndWriteable(dirname($file)); // we have a file or a non-existent directory. Check the next level up.
        }
    }

    private function padArray($array,$size=10){
        return __::map($array,function($x) use($size) {return str_pad($x,$size);});
    }


}