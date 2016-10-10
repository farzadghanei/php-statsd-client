<?php
namespace Statsd\Client\Command;

class Timer extends \Statsd\Client\Command
{
    private $commands = array('timing', 'timingSince');

    public function getCommands()
    {
        return $this->commands;
    }

    private function isClosure($var)
    {
        return is_object($var) && ($var instanceof \Closure);
    }

    public function timing($stat, $delta, $rate=1)
    {
        if ($this->isClosure($delta)) {
            $start_time = gettimeofday(true);
            $delta();
            $end_time = gettimeofday(true);
            $delta = ($end_time - $start_time) * 1000;
        }

        return $this->prepare(
            $stat,
            sprintf('%d|ms', $delta),
            $rate
        );
    }

    /**
     * Send proper timing stats, since the specified starting timestamp.
     * The timing stats will calculated the time passed since the specified
     * timestamp, and send proper metrics.
     *
     * @param string $stat the metric name
     * @param int|float $startTime timestamp of when timing started
     * @param int|float $rate sampling rate (default = 1)
     */
    public function timingSince($stat, $startTime, $rate=1)
    {
        $delta = (gettimeofday(true) - $startTime) * 1000;

        return $this->prepare(
            $stat,
            sprintf('%d|ms', $delta),
            $rate
        );
    }
}
