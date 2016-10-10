<?php
namespace Test\Statsd\Client\Command;

use ReflectionClass;
use Statsd\Client\Command\Timer;

class TimerTest extends \PHPUnit_Framework_TestCase
{
    public function testObject()
    {
        $timer = new Timer();
        $this->assertEquals(
            array('timing', 'timingSince'),
            $timer->getCommands()
        );
    }

    public function testCheckMethodsExistence()
    {
        $timer = new Timer();
        $class = new ReflectionClass('\Statsd\Client\Command\Timer');
        foreach ($timer->getCommands() as $cmd) {
            $method = $class->getMethod($cmd);
        }
    }

    public function testTiming()
    {
        $timer = new Timer();
        $this->assertEquals(
            'foo.bar:10|ms',
            $timer->timing('foo.bar', 10)
        );
    }

    public function testTimingWithClosure()
    {
        $timer = new Timer();
        $result = $timer->timing(
            'foo.bar',
            function () {
                usleep(1000);
            }
        );
        $this->assertRegExp(
            '/foo.bar:\d+|ms/',
            $result
        );
    }

    public function testTimingSince()
    {
        $start = time();
        $timer = new Timer();
        $this->assertRegExp(
            '/foo\.bar\:\d+\|ms/',
            $timer->timingSince('foo.bar', $start)
        );
    }

    /**
     * @dataProvider provideCallableValues
     */
    public function testTimeCallable($callable)
    {
        $this->markTestIncompelete('not implemented yet');
        $timer = new Timer();
        $result = $timer->timeCallable('foo.bar', $callable);
        $this->assertRegExp('/foo.bar:\d+|ms/', $result);
    }

    public function provideCallableValues()
    {
        return array(
            'function name string' => array('phpinfo')
        );
    }
}
