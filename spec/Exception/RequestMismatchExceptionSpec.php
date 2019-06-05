<?php

namespace spec\Http\Mock\Exception;

use Http\Mock\Exception\RequestMismatchException;
use Http\Mock\Exception\UnexpectedRequestException;
use Http\Mock\StrictClient;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\RequestInterface;

class RequestMismatchExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(RequestMismatchException::class);
    }

    function it_is_runtime_exception()
    {
        $this->shouldHaveType(\RuntimeException::class);
    }

    function it_can_be_created_from_matcher_exception()
    {
        $exception = new \Exception('Hmm');

        $this->beConstructedThrough('fromMatcherException', [
            $exception,
        ]);

        $this->shouldHaveType(RequestMismatchException::class);
        $this->getMessage()->shouldReturn('Expected a different request to be sent.');
        $this->getPrevious()->shouldReturn($exception);
    }

    function it_can_be_created_with_create()
    {
        $this->beConstructedThrough('create');

        $this->shouldHaveType(RequestMismatchException::class);
        $this->getMessage()->shouldReturn('Expected a different request to be sent.');
    }
}
