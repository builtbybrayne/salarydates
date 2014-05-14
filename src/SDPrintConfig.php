<?php
namespace Perchten;

/**
 * Utility class to collect file writing and print configuration
 *
 * Class SDPrintConfig
 * @package Perchten
 */
class SDPrintConfig {

    public $file;
    public $printtoconsole = false;
    public $dateformat = "D j/n/Y";
    public $autoCreateFiles = true;

    public function __construct($file = null) {
        $this->file = $file;
    }

}