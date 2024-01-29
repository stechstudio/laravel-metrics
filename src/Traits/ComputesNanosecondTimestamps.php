<?php

namespace STS\Metrics\Traits;

trait ComputesNanosecondTimestamps
{
    /**
     * A public way tog et the nanosecond precision we desire.
     *
     * @param mixed $timestamp
     *
     * @return int|null
     */
    public function getNanoSecondTimestamp($timestamp = null)
    {
        if ($timestamp instanceof \DateTime) {
            return $timestamp->getTimestamp() * 1000000000;
        }

        $length = is_string($timestamp) ? strlen($timestamp) : 0;

        if (is_null($timestamp) || $length < 10) {
           return $this->generateTimestamp();
        }

        if ($length === 19) {
            // Looks like it is already nanosecond precise!
            return $timestamp;
        }

        if ($length === 10) {
            // This appears to be in seconds
            return $timestamp * 1000000000;
        }

        if (preg_match("/\d{10}\.\d{1,4}$/", $timestamp)) {
            // This looks like a microtime float
            return (int)($timestamp * 1000000000);
        }

        // We weren't given a valid timestamp, generate.
        return $this->generateTimestamp();
    }

    /**
     * @return int
     */
    protected function generateTimestamp(): int
    {
        return (int)(microtime(true) * 1000000000);
    }
}
