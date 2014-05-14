<?php
namespace Perchten;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class SDBase {

    const NOTICE_FORMAT = "-- %message%\n";
    const ALERT_FORMAT = "\n\nFATAL ERROR:\t\t%message%\n\n\n";
    protected $months = array("January","February","March","April","May","June","July","August","September","October","November","December");

    protected $logger;
    protected $SDConfig;

    function __construct(SDConfig $SDConfig=null)
    {
        $this->SDConfig = $SDConfig ?: new SDConfig();
        $this->logger = self::getLogger(get_called_class(),$SDConfig);

    }

    public static function getLogger($class,$SDConfig) {

        $formatter = new LineFormatter(null,null,true);

        $logger = new Logger($class);
        if ( !$SDConfig->quiet ) {
            $logLevel = ( $SDConfig->debug ) ? Logger::DEBUG : (($SDConfig->verbose)? Logger::INFO : Logger::NOTICE );
            $logger->pushHandler((new StreamHandler('php://stdout',$logLevel,false))->setFormatter(($SDConfig->debug||$SDConfig->verbose)?$formatter:new LineFormatter(self::NOTICE_FORMAT,null,true)));
            $logger->pushHandler((new StreamHandler('php://stderr',Logger::WARNING,false))->setFormatter($formatter));
        }
        $logger->pushHandler((new StreamHandler('php://stderr',Logger::ALERT,true))->setFormatter(new LineFormatter(SDBase::ALERT_FORMAT,null,true)));
        return $logger;
    }

}