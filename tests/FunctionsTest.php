<?php

use Cppdevcrypto\Dividend;

class FunctionsTest extends TestCase
{
    /**
     * Test divtoshi to dvd converter.
     *
     * @param int    $divtoshi
     * @param string $dividend
     *
     * @return void
     *
     * @dataProvider divtoshiDvdProvider
     */
    public function testToDvd($divtoshi, $dividend)
    {
        $this->assertEquals($dividend, Dividend\to_dividend($divtoshi));
    }

    /**
     * Test dividend to divtoshi converter.
     *
     * @param int    $divtoshi
     * @param string $dividend
     *
     * @return void
     *
     * @dataProvider divtoshiDvdProvider
     */
    public function testToSatoshi($divtoshi, $dividend)
    {
        $this->assertEquals($divtoshi, Dividend\to_divtoshi($dividend));
    }

    /**
     * Test dividend to udvd/bits converter.
     *
     * @param int    $udvd
     * @param string $dividend
     *
     * @return void
     *
     * @dataProvider bitsDvdProvider
     */
    public function testToBits($udvd, $dividend)
    {
        $this->assertEquals($udvd, Dividend\to_udvd($dividend));
    }

    /**
     * Test dividend to mdvd converter.
     *
     * @param float  $mdvd
     * @param string $dividend
     *
     * @return void
     *
     * @dataProvider mdvdDvdProvider
     */
    public function testToMdvd($mdvd, $dividend)
    {
        $this->assertEquals($mdvd, Dividend\to_mdvd($dividend));
    }

    /**
     * Test float to fixed converter.
     *
     * @param float  $float
     * @param int    $precision
     * @param string $expected
     *
     * @return void
     *
     * @dataProvider floatProvider
     */
    public function testToFixed($float, $precision, $expected)
    {
        $this->assertSame($expected, Dividend\to_fixed($float, $precision));
    }

    /**
     * Provides divtoshi and dividend values.
     *
     * @return array
     */
    public function divtoshiDvdProvider()
    {
        return [
            [1000, '0.00001000'],
            [2500, '0.00002500'],
            [-1000, '-0.00001000'],
            [100000000, '1.00000000'],
            [150000000, '1.50000000'],
        ];
    }

    /**
     * Provides divtoshi and udvd/bits values.
     *
     * @return array
     */
    public function bitsDvdProvider()
    {
        return [
            [10, '0.00001000'],
            [25, '0.00002500'],
            [-10, '-0.00001000'],
            [1000000, '1.00000000'],
            [1500000, '1.50000000'],
        ];
    }

    /**
     * Provides divtoshi and mdvd values.
     *
     * @return array
     */
    public function mdvdDvdProvider()
    {
        return [
            [0.01, '0.00001000'],
            [0.025, '0.00002500'],
            [-0.01, '-0.00001000'],
            [1000, '1.00000000'],
            [1500, '1.50000000'],
        ];
    }

    /**
     * Provides float values with precision and result.
     *
     * @return array
     */
    public function floatProvider()
    {
        return [
            [1.2345678910, 0, '1'],
            [1.2345678910, 2, '1.23'],
            [1.2345678910, 4, '1.2345'],
            [1.2345678910, 8, '1.23456789'],
        ];
    }
}
