<?php

namespace spec\Http\Mock\Exception;

use Http\Mock\Exception\UnexpectedRequestException;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\RequestInterface;

class UnexpectedRequestExceptionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(UnexpectedRequestException::class);
    }

    function it_is_runtime_exception()
    {
        $this->shouldHaveType(\RuntimeException::class);
    }

    function it_can_be_created_from_request(RequestInterface $request)
    {
        $this->beConstructedThrough('fromRequest', [
            $request,
        ]);

        $this->shouldHaveType(UnexpectedRequestException::class);
        $this->getMessage()->shouldReturn('Did not expect request to be sent');
        $this->getRequest()->shouldReturn($request->getWrappedObject());
    }
}
