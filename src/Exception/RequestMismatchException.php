<?php

namespace Http\Mock\Exception;

class RequestMismatchException extends \RuntimeException
{
    /**
     * @param \Exception $exception
     *
     * @return self
     */
    public static function fromMatcherException(\Exception $exception)
    {
        return new self(
            'Expected a different request to be sent.',
            0,
            $exception
        );
    }

    /**
     * @return self
     */
    public static function create()
    {
        return new self('Expected a different request to be sent.');
    }
}
