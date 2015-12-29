<?php

namespace Http\Adapter\Mock;

use Http\Client\Common\HttpAsyncClientEmulator;
use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Discovery\MessageFactoryDiscovery;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * HTTP client mock
 *
 * This mock is most useful in tests. It does not send requests but stores them
 * for later retrieval. Additionally, you can set an exception to test
 * exception handling.
 *
 * @author David de Boer <david@ddeboer.nl>
 */
class Client implements HttpClient, HttpAsyncClient
{
    use HttpAsyncClientEmulator;

    private $requests = [];
    private $responses = [];
    private $exceptions = [];

    /**
     * {@inheritdoc}
     */
    public function sendRequest(RequestInterface $request)
    {
        $this->requests[] = $request;

        if (count($this->exceptions) > 0) {
            throw array_shift($this->exceptions);
        }

        if (count($this->responses) > 0) {
            return array_shift($this->responses);
        }

        // Return success response by default
        return MessageFactoryDiscovery::find()->createResponse();
    }

    /**
     * Add exception that will be thrown
     *
     * @param \Exception $exception
     */
    public function addException(\Exception $exception)
    {
        $this->exceptions[] = $exception;
    }

    /**
     * Add response that will be returned
     *
     * @param ResponseInterface $response
     */
    public function addResponse(ResponseInterface $response)
    {
        $this->responses[] = $response;
    }

    /**
     * Get requests that were sent
     *
     * @return RequestInterface[]
     */
    public function getRequests()
    {
        return $this->requests;
    }
}
