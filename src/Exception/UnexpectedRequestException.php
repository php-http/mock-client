<?php

namespace Http\Mock\Exception;

use Psr\Http\Message\RequestInterface;

class UnexpectedRequestException extends \RuntimeException
{
    /**
     * @var RequestInterface|null
     */
    private $request;

    /**
     * @param RequestInterface $request
     *
     * @return self
     */
    public static function fromRequest(RequestInterface $request)
    {
        $instance = new self('Did not expect request to be sent');

        $instance->request = $request;

        return $instance;
    }

    /**
     * @return RequestInterface|null
     */
    public function getRequest()
    {
        return $this->request;
    }
}
