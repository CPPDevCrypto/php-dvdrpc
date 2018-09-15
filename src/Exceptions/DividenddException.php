<?php

namespace Cppdevcrypto\Dividend\Exceptions;

use RuntimeException;

class DividenddException extends RuntimeException
{
    /**
     * Constructs new Dividendd exception.
     *
     * @param object $error
     *
     * @return void
     */
    public function __construct($error)
    {
        parent::__construct($error['message'], $error['code']);
    }
}
