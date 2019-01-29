<?php

namespace Lanin\Laravel\ApiDebugger\Events;

use Symfony\Component\HttpFoundation\Request;
use Illuminate\Http\Request as IlluminateRequest;
use Symfony\Component\HttpFoundation\Response;

class RequestHandled
{
    /**
     * @var Request|IlluminateRequest $request
     */
    public $request;

    /**
     * @var Response $response
     */
    public $response;

    /**
     * Create a new event instance.
     *
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }
}
