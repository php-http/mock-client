<?php

namespace spec\Http\Mock;

use Http\Client\HttpAsyncClient;
use Http\Client\HttpClient;
use Http\Message\RequestMatcher;
use Http\Message\ResponseFactory;
use Http\Mock\Client;
use Http\Mock\Exception\RequestMismatchException;
use Http\Mock\Exception\UnexpectedRequestException;
use Http\Mock\StrictClient;
use PhpSpec\Matcher\MatcherInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use PhpSpec\ObjectBehavior;

/**
 * @mixin StrictClient
 */
class StrictClientSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(StrictClient::class);
    }

    function it_is_an_http_client()
    {
        $this->shouldImplement(HttpClient::class);
    }

    function it_is_an_async_http_client()
    {
        $this->shouldImplement(HttpAsyncClient::class);
    }

    function it_throws_when_no_matchers_have_been_configured(RequestInterface $request)
    {
        $this
            ->shouldThrow(UnexpectedRequestException::fromRequest($request->getWrappedObject()))
            ->duringSendRequest($request);
    }

    function it_throws_when_next_request_matcher_throws_exception(
        RequestMatcher $matcher,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $exception = new \Exception('Failed asserting that request method is "POST", got "GET" instead.');

        $matcher->matches($request)->willThrow($exception);

        $this->on($matcher, $response);

        $this
            ->shouldThrow(RequestMismatchException::fromMatcherException($exception))
            ->duringSendRequest($request);
    }

    function it_throws_when_next_request_matcher_does_not_match_request(
        RequestMatcher $matcher,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $matcher->matches($request)->willReturn(false);

        $this->on($matcher, $response);

        $this
            ->shouldThrow(RequestMismatchException::create())
            ->duringSendRequest($request);
    }

    function it_throws_configured_exception_when_next_request_matcher_matches_request(
        RequestMatcher $matcher,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $exception = new \Exception('Sending the request failed because of a network error');

        $matcher->matches($request)->willReturn(true);

        $this->on($matcher, $exception);

        $this
            ->shouldThrow($exception)
            ->duringSendRequest($request);
    }

    function it_returns_configured_request_when_next_request_matcher_matches_request(
        RequestMatcher $matcher,
        RequestInterface $request,
        ResponseInterface $response
    ) {
        $matcher->matches($request)->willReturn(true);

        $this->on($matcher, $response);

        $this->sendRequest($request)->shouldReturn($response);
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

    function it_has_completed_sequence_when_no_matches_have_been_configured()
    {
        $this->hasCompletedSequence()->shouldReturn(true);
    }

    function it_has_not_completed_sequence_when_not_all_expected_requests_have_been_sent(
        RequestMatcher $firstMatcher,
        RequestInterface $firstRequest,
        ResponseInterface $firstResponse,
        RequestMatcher $secondMatcher,
        RequestInterface $secondRequest,
        ResponseInterface $secondResponse
    ) {
        $firstMatcher->matches($firstRequest)->willReturn(true);

        $this->on($firstMatcher, $firstResponse);

        $secondMatcher->matches($secondRequest)->willReturn(true);

        $this->on($secondMatcher, $secondResponse);

        $this->sendRequest($firstRequest);

        $this->hasCompletedSequence()->shouldReturn(false);
    }

    function it_has_completed_sequence_when_all_expected_requests_have_been_sent(
        RequestMatcher $firstMatcher,
        RequestInterface $firstRequest,
        ResponseInterface $firstResponse,
        RequestMatcher $secondMatcher,
        RequestInterface $secondRequest,
        ResponseInterface $secondResponse
    ) {
        $firstMatcher->matches($firstRequest)->willReturn(true);

        $this->on($firstMatcher, $firstResponse);

        $secondMatcher->matches($secondRequest)->willReturn(true);

        $this->on($secondMatcher, $secondResponse);

        $this->sendRequest($firstRequest);
        $this->sendRequest($secondRequest);

        $this->hasCompletedSequence()->shouldReturn(true);
    }
}
