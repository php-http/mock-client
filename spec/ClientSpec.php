<?php

namespace spec\Http\Mock;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Message\RequestMatcher;
use Http\Message\ResponseFactory;
use Http\Mock\Client;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use PhpSpec\ObjectBehavior;

class ClientSpec extends ObjectBehavior
{
    function let(ResponseFactory $responseFactory)
    {
        $this->beConstructedWith($responseFactory);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(Client::class);
    }

    function it_is_an_http_client()
    {
        $this->shouldImplement(HttpClient::class);
    }

    function it_is_an_async_http_client()
    {
        $this->shouldImplement(HttpAsyncClient::class);
    }

    function it_returns_a_response_for_a_request(RequestInterface $request, ResponseInterface $response)
    {
        $this->addResponse($response);

        $this->sendRequest($request)->shouldReturn($response);
    }

    function it_returns_the_default_response_for_a_request(RequestInterface $request, ResponseInterface $response)
    {
        $this->setDefaultResponse($response);

        $this->sendRequest($request)->shouldReturn($response);
    }

    function it_throws_an_exception_for_a_request(RequestInterface $request)
    {
        $this->addException(new \Exception());

        $this->shouldThrow('Exception')->duringSendRequest($request);
    }

    function it_throws_the_default_exception_for_a_request(RequestInterface $request)
    {
        $this->setDefaultException(new \Exception());

        $this->shouldThrow('Exception')->duringSendRequest($request);
    }

    function it_creates_an_empty_response_when_none_is_added(
        RequestInterface $request,
        ResponseFactory $responseFactory,
        ResponseInterface $response
    ) {
        $responseFactory->createResponse()->willReturn($response);

        $this->sendRequest($request)->shouldReturn($response);
    }

    function it_returns_the_last_request(RequestInterface $request, ResponseInterface $response)
    {
        // we need to set something that sendRequest can return.
        $this->addResponse($response);

        $this->sendRequest($request);

        $this->getLastRequest()->shouldReturn($request);
    }

    function it_returns_false_when_there_is_no_last_request()
    {
        $this->getLastRequest()->shouldReturn(false);
    }

    function it_reset(
        ResponseFactory $responseFactory,
        RequestInterface $request,
        ResponseInterface $response,
        ResponseInterface $newResponse
    ) {
        $this->addResponse($response);
        $this->setDefaultResponse($response);
        $this->addException(new \Exception());
        $this->setDefaultException(new \Exception());

        $responseFactory->createResponse()->willReturn($newResponse);

        $this->reset();

        $this->sendRequest($request)->shouldReturn($newResponse);

        $this->reset();

        $this->getRequests()->shouldReturn([]);
    }
    function it_returns_response_if_request_matcher_matches(
        RequestMatcher $matcher,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $matcher->matches($request)->willReturn(true);
        $this->on($matcher, $response);
        $this->sendRequest($request)->shouldReturn($response);
    }

    function it_throws_exception_if_request_matcher_matches(
        RequestMatcher $matcher,
        RequestInterface $request
    ) {
        $matcher->matches($request)->willReturn(true);
        $this->on($matcher, new \Exception());
        $this->shouldThrow('Exception')->duringSendRequest($request);
    }

    function it_skips_conditional_response_if_matcher_returns_false(
        RequestMatcher $matcher,
        RequestInterface $request,
        ResponseInterface $expectedResponse,
        ResponseInterface $skippedResponse
    ) {
        $matcher->matches($request)->willReturn(false);
        $this->on($matcher, $skippedResponse);
        $this->addResponse($expectedResponse);
        $this->sendRequest($request)->shouldReturn($expectedResponse);
    }

    function it_calls_callable_with_request_as_argument_when_matcher_returns_true(
        RequestMatcher $matcher,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $matcher->matches($request)->willReturn(true);

        $this->on(
            $matcher,
            function(RequestInterface $request) use ($response) {
                return $response->getWrappedObject();
            }
        );

        $this->sendRequest($request)->shouldReturn($response);
    }
}
