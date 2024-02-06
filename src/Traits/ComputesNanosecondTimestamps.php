<?php

namespace STS\Metrics\Traits;

trait ComputesNanosecondTimestamps
{
    protected const TO_NANO = 1000000000;

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
            return $timestamp->getTimestamp() * self::TO_NANO;
        } elseif (is_int($timestamp)) {
            $length = strlen((string) $timestamp);

            return match ($length) {
                // Looks like it is already nanosecond precise!
                19 => $timestamp,
                // This appears to be in seconds
                10 => $timestamp * self::TO_NANO,
                default => $this->generateTimestamp(),
            };
        } elseif (is_string($timestamp)) {
            if (preg_match("/\d{10}\.\d{1,4}$/", $timestamp)) {
                return (int) ($timestamp * self::TO_NANO);
            } elseif (ctype_digit($timestamp)) {
                $length = strlen($timestamp);

                return match ($length) {
                    // Looks like it is already nanosecond precise!
                    19 => (int) $timestamp,
                    // This appears to be in seconds
                    10 => (int) ($timestamp * self::TO_NANO),
                    default => $this->generateTimestamp(),
                };
            }
        } elseif (is_float($timestamp)) {
            $integerLength = (int) floor(log10(abs($timestamp))) + 1;

            return match ($integerLength) {
                // Looks like it is already nanosecond precise!
                19 => (int) $timestamp,
                // This appears to be in seconds
                10 => (int) ($timestamp * self::TO_NANO),
                default => $this->generateTimestamp(),
            };
        }

        // We weren't given a valid timestamp, generate.
        return $this->generateTimestamp();
    }

    /**
     * @return int
     */
    protected function generateTimestamp(): int
    {
        return (int) (microtime(true) * self::TO_NANO);
    }
}
