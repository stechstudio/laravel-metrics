<?php

use STS\Metrics\Drivers\InfluxDB;

class InfluxDBV2DriverTest extends TestCase
{

    public function testUdpWriteClient()
    {
        $this->setupInfluxDB2(['tcp_port' => 123, 'udp_port' => 456], false);

        $this->assertInstanceOf(
            \InfluxDB2\UdpWriter::class,
            app(InfluxDB::class)->getWriteConnection()
        );
    }

    public function testTcpWriteClient()
    {
        $this->setupInfluxDB2(['tcp_port' => 123]);

        $this->assertInstanceOf(
            \InfluxDB2\WriteApi::class,
            app(InfluxDB::class)->getWriteConnection()
        );
    }

    /**
     * @dataProvider nanoSecondTimestampInvalid
     */
    public function testNanoSecondTimestampInvalid($input)
    {
        $this->setupInfluxDB();

        $influx = app(InfluxDB::class);

        $now = $influx->getNanoSecondTimestamp();
        $result = $influx->getNanoSecondTimestamp($input);

        $this->assertTrue(is_int($result));
        $this->assertEquals(19, strlen((string) $result));
        $this->assertGreaterThanOrEqual($now, $result);
    }

    /**
     * @dataProvider nanoSecondTimestampValid
     */
    public function testNanoSecondTimestamp($expected, $input)
    {
        $this->setupInfluxDB();

        $influx = app(InfluxDB::class);

        $result = $influx->getNanoSecondTimestamp($input);

        $this->assertTrue(is_int($result));
        $this->assertEquals(19, strlen((string) $result));
        $this->assertEquals($expected, $result);
    }

    public static function nanoSecondTimestampValid()
    {
        $expected = 1508713728000000000;
        $expectedPrecise = 1508713728123400000;

        return [
            [$expected, 1508713728000000000,],
            [$expected, 1508713728,],
            [$expected, '1508713728000000000',],
            [$expected, '1508713728',],
            [$expected, new \DateTime('@1508713728'),],
            [$expected, '1508713728.0000',],
            [$expected, '1508713728.000',],
            [$expected, '1508713728.00',],
            [$expected, '1508713728.0',],
            [$expected, 1508713728.0000,],
            [$expected, 1508713728.000,],
            [$expected, 1508713728.00,],
            [$expected, 1508713728.0,],
            [$expectedPrecise, 1508713728123400000,],
            [$expectedPrecise, '1508713728123400000',],
            // [$expectedPrecise, '1508713728.1234',], // PHP float precision breaks this
            // [1508713728123000000, '1508713728.123',], // PHP float precision breaks this
            [1508713728120000000, '1508713728.12',],
            [1508713728100000000, '1508713728.1',],
            // [1508713728123400000, 1508713728.1234,], // PHP float precision breaks this
            // [1508713728123000000, 1508713728.123,], // PHP float precision breaks this
            [1508713728120000000, 1508713728.12,],
            [1508713728100000000, 1508713728.1,],
        ];
    }

    public static function nanoSecondTimestampInvalid()
    {
        return [
            ['abc'], // letters
            ['150871372800000000a',], // numbers with letters
            [150871372800000000,], // 18 digits
            [15087137281,], // 11 digits
            [150871372,], // 9 digits
            [15087137,], // 8 digits
            [0,],
            [0.0,],
            ['000000000.1',],
            ['000000000.0',],
        ];
    }
}