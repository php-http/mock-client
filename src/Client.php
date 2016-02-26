<?php

namespace Http\Mock;

use Http\Client\Common\HttpAsyncClientEmulator;
use Http\Client\Exception;
use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\ResponseFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * HTTP client mock.
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

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var RequestInterface[]
     */
    private $requests = [];

    /**
     * @var ResponseInterface[]
     */
    private $responses = [];

    /**
     * @var Exception[]
     */
    private $exceptions = [];

    /**
     * @param ResponseFactory|null $responseFactory
     */
    public function __construct(ResponseFactory $responseFactory = null)
    {
        $this->responseFactory = $responseFactory ?: MessageFactoryDiscovery::find();
    }

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
        return $this->responseFactory->createResponse();
    }

    /**
     * Adds an exception that will be thrown.
     *
     * @param \Exception $exception
     */
    public function addException(\Exception $exception)
    {
        $this->exceptions[] = $exception;
    }

    /**
     * Adds a response that will be returned.
     *
     * @param ResponseInterface $response
     */
    public function addResponse(ResponseInterface $response)
    {
        $this->responses[] = $response;
    }

    /**
     * Returns requests that were sent.
     *
     * @return RequestInterface[]
     */
    public function getRequests()
    {
        return $this->requests;
    }
}
