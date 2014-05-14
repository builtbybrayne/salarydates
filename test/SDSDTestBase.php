<?php
namespace Perchten;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use PHPUnit_Framework_TestCase;
use ReflectionClass;

/**
 * Class TestBase from https://github.com/jenwachter/phpunit-test-skelaton
 */
abstract class SDTestBase extends PHPUnit_Framework_TestCase
{
    protected $testClass;
    protected $reflection;
    protected $logger;

    public function setUp()
    {
        if ( $this->testClass ) $this->reflection = new ReflectionClass($this->testClass);
        $this->logger = new Logger(get_called_class());
        $this->logger->pushHandler(new StreamHandler('php://stdout',Logger::DEBUG,false));
        $this->logger->pushHandler(new StreamHandler('php://stderr',Logger::WARNING,false));
    }

    public function getMethod($method)
    {
        $method = $this->reflection->getMethod($method);
        $method->setAccessible(true);

        return $method;
    }

    public function getProperty($property)
    {
        $property = $this->reflection->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($this->testClass);
    }

    public function setProperty($property, $value)
    {
        $property = $this->reflection->getProperty($property);
        $property->setAccessible(true);

        return $property->setValue($this->testClass, $value);
    }

    protected function getQuietSDConfig() {
        $SDConfig = new SDConfig();
        $SDConfig->quiet = true;
        return $SDConfig;
    }
}