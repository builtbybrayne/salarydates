<?php
namespace Perchten;

class SDPrintConfig {

    public $file;
    public $printtoconsole = false;
    public $format = "D j/n/Y";
    public $autoCreateFiles = true;

    public function __construct($file = null) {
        $this->file = $file;
    }

}