<?php

namespace Cppdevcrypto\Dividend;

if (!function_exists('to_dividend')) {
    /**
     * Converts from divtoshi to dividend.
     *
     * @param int $divtoshi
     *
     * @return string
     */
    function to_dividend($divtoshi)
    {
        return bcdiv((int) $divtoshi, 1e8, 8);
    }
}

if (!function_exists('to_divtoshi')) {
    /**
     * Converts from dividend to divtoshi.
     *
     * @param float $dividend
     *
     * @return string
     */
    function to_divtoshi($dividend)
    {
        return bcmul(to_fixed($dividend, 8), 1e8);
    }
}

if (!function_exists('to_udvd')) {
    /**
     * Converts from dividend to udvd/bits.
     *
     * @param float $dividend
     *
     * @return string
     */
    function to_udvd($dividend)
    {
        return bcmul(to_fixed($dividend, 8), 1e6, 4);
    }
}

if (!function_exists('to_mdvd')) {
    /**
     * Converts from dividend to mdvd.
     *
     * @param float $dividend
     *
     * @return string
     */
    function to_mdvd($dividend)
    {
        return bcmul(to_fixed($dividend, 8), 1e3, 4);
    }
}

if (!function_exists('to_fixed')) {
    /**
     * Brings number to fixed precision without rounding.
     *
     * @param float $number
     * @param int   $precision
     *
     * @return string
     */
    function to_fixed($number, $precision = 8)
    {
        $number = $number * pow(10, $precision);

        return bcdiv($number, pow(10, $precision), $precision);
    }
}
