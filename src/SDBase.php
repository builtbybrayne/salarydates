<?php
namespace Perchten;

use Monolog\Formatter\LineFormatter;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * Parent class for the main workhorse classes. Mainly exposes logging utilities
 *
 * Class SDBase
 * @package Perchten
 */
class SDBase {

    const NOTICE_FORMAT = "-- %message%\n";
    const ALERT_FORMAT = "\n\nFATAL ERROR:\t\t%message%\n\n\n";
    protected $months = array("January","February","March","April","May","June","July","August","September","October","November","December");

    protected $logger;
    protected $SDConfig;

    /**
     * @param SDConfig $SDConfig
     */
    function __construct(SDConfig $SDConfig=null)
    {
        $this->SDConfig = $SDConfig ?: new SDConfig();
        $this->logger = self::getLogger(get_called_class(),$SDConfig);

    }

    /**
     * Called from the constructor by default, but also statically exposed as a factor for one-off logging requirements
     *
     * Standard logger operates as follows:
     *
     * If $debug is set, all log level are displayed
     * If $verbose is set, all log levels from INFO upwards are displayed
     * If $quiet is set, all log levels below ALERT are supressed
     * If no specific configuration is given, all log levels from NOTICE upwards are printed.
     *
     * $debug and $verbose also change the format of the display to show additional information about where the logging call came from
     *
     * @param $class
     * @param $SDConfig
     * @return Logger
     */
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