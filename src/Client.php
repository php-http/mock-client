<?php

namespace Http\Mock;

use Http\Client\Common\HttpAsyncClientEmulator;
use Http\Client\Exception;
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
class Client implements ClientInterface
{
    use HttpAsyncClientEmulator;

    /**
     * @var ResponseFactory
     */
    protected $responseFactory;

    /**
     * @var RequestInterface[]
     */
    protected $requests = [];

    /**
     * @var ResponseInterface[]
     */
    protected $responses = [];

    /**
     * @var ResponseInterface|null
     */
    protected $defaultResponse;

    /**
     * @var Exception[]
     */
    protected $exceptions = [];

    /**
     * @var Exception|null
     */
    protected $defaultException;

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

        if ($this->defaultException) {
            throw $this->defaultException;
        }

        if ($this->defaultResponse) {
            return $this->defaultResponse;
        }

        // Return success response by default
        return $this->responseFactory->createResponse();
    }

    /**
     * {@inheritdoc}
     */
    public function addException(\Exception $exception)
    {
        $this->exceptions[] = $exception;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultException(\Exception $defaultException = null)
    {
        $this->defaultException = $defaultException;
    }

    /**
     * {@inheritdoc}
     */
    public function addResponse(ResponseInterface $response)
    {
        $this->responses[] = $response;
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultResponse(ResponseInterface $defaultResponse = null)
    {
        $this->defaultResponse = $defaultResponse;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequests()
    {
        return $this->requests;
    }
}
