<?php

namespace Http\Mock;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * HTTP Mock Client interface.
 *
 * @author Dezső Biczó <mxr576@gmail.com>
 */
interface ClientInterface extends HttpClient, HttpAsyncClient
{
    /**
     * Adds an exception that will be thrown.
     *
     * @param \Exception $exception
     */
    public function addException(\Exception $exception);

    /**
     * Sets the default exception to throw when the list of added exceptions and responses is exhausted.
     *
     * If both a default exception and a default response are set, the exception will be thrown.
     *
     * @param \Exception|null $defaultException
     */
    public function setDefaultException(\Exception $defaultException = null);

    /**
     * Adds a response that will be returned.
     *
     * @param ResponseInterface $response
     */
    public function addResponse(ResponseInterface $response);

    /**
     * Sets the default response to be returned when the list of added exceptions and responses is exhausted.
     *
     * @param ResponseInterface|null $defaultResponse
     */
    public function setDefaultResponse(ResponseInterface $defaultResponse = null);

    /**
     * Returns requests that were sent.
     *
     * @return RequestInterface[]
     */
    public function getRequests();
}
