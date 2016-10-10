<?php
namespace Test\Statsd;

use Exception;
use Statsd\Client;
use Statsd\Client\Command\Counter;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function testClientWithDefaultSettings()
    {
        $statsd = new Client();
        $this->assertEquals(
            '',
            $statsd->getPrefix()
        );

        $all_settings = $statsd->getSettings();
        $this->assertFalse(
            $all_settings['throw_exception']
        );
    }

    public function testClientWithOverideSettings()
    {
        $statsd = new Client(
            array(
                'prefix' => 'foo.bar',
            )
        );

        $this->assertEquals(
            'foo.bar',
            $statsd->getPrefix()
        );
    }

    /**
     * @expectedException BadFunctionCallException
     * @expectedExceptionMessage Call to undefined method Statsd\Client::fooFunc()
     */
    public function testClientWithWrongCommand()
    {
        $statsd = new Client();
        $statsd->fooFunc("foo", "bar");
    }

    public function getMockUpSocketConnection()
    {
        return $this->getMock(
            '\Statsd\Client\SocketConnection',
            array(
                'send'
            ),
            array(
                array(
                    'throw_exception' => false,
                    'host' => 'foo.bar',
                )
            )
        );
    }

    public function testClientAddCommand()
    {
        $sc = $this->getMockUpSocketConnection();
        $sc->expects($this->once())
            ->method('send')
            ->with("foo.bar:1|c");

        $statsd = new Client(array('connection' => $sc));
        $statsd->addCommand(
            new Counter()
        );

        $this->assertInstanceOf(
            '\Statsd\Client',
            $statsd->incr('foo.bar', 1)
        );
    }

    public function testClientCallCommandWithPrefix()
    {
        $sc = $this->getMockUpSocketConnection();
        $sc->expects($this->once())
            ->method('send')
            ->with("top.foo.bar:1|c");

        $statsd = new Client(array('connection' => $sc));
        $statsd->addCommand(
            new Counter()
        );
        $statsd->setPrefix('top');

        $this->assertInstanceOf(
            '\Statsd\Client',
            $statsd->incr('foo.bar', 1)
        );
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage DUMMY EXCEPTION
     */
    public function testClientCallCommandWithException()
    {
        $cmd = $this->getMock(
            '\Statsd\Client\Command\Counter',
            array('incr')
        );

        $cmd->expects($this->once())
            ->method('incr')
            ->will($this->throwException(new Exception("DUMMY EXCEPTION")));

        $statsd = new Client(array('throw_exception'=> true));
        $statsd->addCommand($cmd);
        $statsd->__call('incr', array('foo.bar', 1));
    }

    public function testChaingCall()
    {
        $statsd = new Client();
        $result = $statsd->incr('foo.bar')
            ->decr('foo.bar')
            ->gauge('foo.bar', 10);

        $this->assertInstanceOf(
            '\Statsd\Client',
            $result
        );
    }
}
