<?php

namespace spec\Http\Mock;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
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
}
