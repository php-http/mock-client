<?php

namespace Http\Mock;

use Http\Client\Common\HttpAsyncClientEmulator;
use Http\Client\Common\VersionBridgeClient;
use Http\Client\Exception;
use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Message\RequestMatcher;
use Http\Mock\Exception\RequestMismatchException;
use Http\Mock\Exception\UnexpectedRequestException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * An implementation of the HTTP client that is useful for automated tests.
 *
 * This mock expects requests to be sent in a sequence and returns responses or throws exceptions as configured.
 *
 * @author Andreas Möller <am@localheinz.com>
 */
class StrictClient implements HttpClient, HttpAsyncClient
{
    use HttpAsyncClientEmulator;
    use VersionBridgeClient;

    /**
     * @var array
     */
    private $configuredSequence = [];

    /**
     * {@inheritdoc}
     *
     * @throws UnexpectedRequestException
     * @throws RequestMismatchException
     */
    public function doSendRequest(RequestInterface $request)
    {
        $next = array_shift($this->configuredSequence);

        if (null === $next) {
            throw UnexpectedRequestException::fromRequest($request);
        }

        /** @var RequestMatcher $matcher */
        $matcher = $next['matcher'];

        try {
            $isMatch = $matcher->matches($request);
        } catch (\Exception $exception) {
            throw RequestMismatchException::fromMatcherException($exception);
        }

        if (false === $isMatch) {
            throw RequestMismatchException::create();
        }

        /** @var callable $callable */
        $callable = $next['callable'];

        return $callable($request);
    }

    /**
     * Adds an exception to be thrown or response to be returned for the next request
     * expected to be sent in a sequence of requests.
     *
     * For more complex logic, pass a callable as $result. The method is given
     * the request and MUST either return a ResponseInterface or throw an
     * exception that implements the PSR-18 / HTTPlug exception interface.
     *
     * @param ResponseInterface|Exception|ClientExceptionInterface|callable $result
     */
    public function on(RequestMatcher $requestMatcher, $result)
    {
        $callable = null;

        switch (true) {
            case is_callable($result):
                $callable = $result;

                break;
            case $result instanceof ResponseInterface:
                $callable = function () use ($result) {
                    return $result;
                };

                break;
            case $result instanceof \Exception:
                $callable = function () use ($result) {
                    throw $result;
                };

                break;
            default:
                throw new \InvalidArgumentException('Result must be either a response, an exception, or a callable');
        }

        $this->configuredSequence[] = [
            'matcher' => $requestMatcher,
            'callable' => $callable,
        ];
    }

    /**
     * Returns true when the configured sequence of requests and responses (or exceptions)
     * has been completed, i.e., the queue of configured results has been exhausted.
     *
     * @return bool
     */
    public function hasCompletedSequence()
    {
        return 0 === count($this->configuredSequence);
    }
}
